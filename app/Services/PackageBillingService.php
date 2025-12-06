<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\GlobalInvoice;
use App\Models\SuperAdmin\GlobalSubscription;
use App\Models\MarketplaceModule;
use App\Models\CompanyModuleSubscription;
use App\Models\AICreditPackage;
use App\Models\AICreditPurchase;
use App\Services\AICreditsService;
use App\Services\ModuleAddonService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PackageBillingService
{
    protected AICreditsService $aiCreditsService;
    protected ModuleAddonService $moduleAddonService;

    public function __construct(AICreditsService $aiCreditsService, ModuleAddonService $moduleAddonService)
    {
        $this->aiCreditsService = $aiCreditsService;
        $this->moduleAddonService = $moduleAddonService;
    }

    /**
     * Calculate total billing amount for package + add-ons + credits
     */
    public function calculateBillingAmount(
        Package $package,
        array $moduleAddonIds = [],
        ?int $aiCreditPackageId = null,
        ?int $promotionId = null,
        string $billingCycle = 'monthly'
    ): array {
        $items = [];
        $total = 0;

        // Base package price
        $packagePrice = $billingCycle === 'annual' ? (float) $package->annual_price : (float) $package->monthly_price;
        if ($packagePrice > 0) {
            $items[] = [
                'type' => 'package',
                'name' => $package->name,
                'amount' => $packagePrice,
                'billing_cycle' => $billingCycle,
            ];
            $total += $packagePrice;
        }

        // Module add-ons
        foreach ($moduleAddonIds as $moduleId) {
            $module = MarketplaceModule::find($moduleId);
            if (!$module || !$module->is_active) {
                continue;
            }

            $addonPrice = $this->getModulePriceForBillingCycle($module, $billingCycle);
            if ($addonPrice > 0) {
                $items[] = [
                    'type' => 'module_addon',
                    'name' => $module->name,
                    'module_id' => $module->id,
                    'amount' => $addonPrice,
                    'billing_cycle' => $billingCycle,
                ];
                $total += $addonPrice;
            }
        }

        // AI Credits (one-time purchase)
        $aiCreditsAmount = 0;
        $aiCreditsBonus = 0;
        if ($aiCreditPackageId) {
            $creditPackage = AICreditPackage::find($aiCreditPackageId);
            if ($creditPackage && $creditPackage->is_active) {
                $aiCreditsAmount = (float) $creditPackage->price;

                // Check for promotion
                if ($promotionId) {
                    $promotion = \App\Models\AICreditPromotion::find($promotionId);
                    if ($promotion) {
                        $aiCreditsBonus = $this->aiCreditsService->calculateBonusCredits($aiCreditsAmount, $promotion);
                    }
                }

                $items[] = [
                    'type' => 'ai_credits',
                    'name' => "AI Credits - {$creditPackage->name}",
                    'package_id' => $creditPackage->id,
                    'amount' => $aiCreditsAmount,
                    'bonus_credits' => $aiCreditsBonus,
                    'billing_cycle' => 'one_time',
                ];
                $total += $aiCreditsAmount;
            }
        }

        return [
            'items' => $items,
            'subtotal' => $total,
            'total' => $total,
            'currency_id' => $package->currency_id,
        ];
    }

    /**
     * Get module price for billing cycle
     */
    protected function getModulePriceForBillingCycle(MarketplaceModule $module, string $billingCycle): float
    {
        return match ($billingCycle) {
            'annual' => (float) ($module->price_annual ?? 0),
            'monthly' => (float) ($module->price_monthly ?? 0),
            default => (float) ($module->price_monthly ?? 0),
        };
    }

    /**
     * Create unified subscription with package + add-ons + credits
     */
    public function createUnifiedSubscription(
        Company $company,
        Package $package,
        array $moduleAddonIds = [],
        ?int $aiCreditPackageId = null,
        ?int $promotionId = null,
        string $billingCycle = 'monthly',
        string $paymentGateway = 'offline'
    ): ?GlobalSubscription {
        try {
            DB::beginTransaction();

            $billingAmount = $this->calculateBillingAmount($package, $moduleAddonIds, $aiCreditPackageId, $promotionId, $billingCycle);

            // Create or update subscription
            $subscription = GlobalSubscription::updateOrCreate(
                [
                    'company_id' => $company->id,
                ],
                [
                    'package_id' => $package->id,
                    'package_type' => $billingCycle,
                    'subscription_type' => 'unified', // New type for unified billing
                    'status' => 'active',
                    'payment_gateway' => $paymentGateway,
                ]
            );

            // Add module add-ons
            foreach ($moduleAddonIds as $moduleId) {
                $module = MarketplaceModule::find($moduleId);
                if ($module) {
                    $this->moduleAddonService->addModuleAddon(
                        $company,
                        $module,
                        $billingCycle === 'annual' ? 'annual' : 'monthly',
                        true
                    );
                }
            }

            // Purchase AI credits if provided
            if ($aiCreditPackageId) {
                $creditPackage = AICreditPackage::find($aiCreditPackageId);
                if ($creditPackage) {
                    $promotion = $promotionId ? \App\Models\AICreditPromotion::find($promotionId) : null;
                    $this->aiCreditsService->purchaseCredits($company, $creditPackage, $promotion);
                }
            }

            DB::commit();

            return $subscription;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unified Subscription Creation Error: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'package_id' => $package->id,
            ]);

            return null;
        }
    }

    /**
     * Create unified invoice
     */
    public function createUnifiedInvoice(
        Company $company,
        Package $package,
        array $billingItems,
        string $paymentGateway = 'offline',
        ?string $gatewayTransactionId = null
    ): ?GlobalInvoice {
        try {
            DB::beginTransaction();

            $subtotal = array_sum(array_column($billingItems, 'amount'));
            $total = $subtotal;

            // Create invoice
            $invoice = GlobalInvoice::create([
                'company_id' => $company->id,
                'package_id' => $package->id,
                'subscription_type' => 'unified',
                'currency_id' => $package->currency_id,
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => 'paid',
                'payment_gateway' => $paymentGateway,
                'gateway_transaction_id' => $gatewayTransactionId,
                'paid_on' => now(),
                'invoice_items' => json_encode($billingItems),
            ]);

            DB::commit();

            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unified Invoice Creation Error: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'package_id' => $package->id,
            ]);

            return null;
        }
    }

    /**
     * Add module add-on to existing subscription
     */
    public function addModuleAddonToSubscription(Company $company, MarketplaceModule $module, string $billingCycle = 'monthly'): bool
    {
        try {
            DB::beginTransaction();

            $subscription = GlobalSubscription::where('company_id', $company->id)->first();
            if (!$subscription) {
                DB::rollBack();

                return false;
            }

            $this->moduleAddonService->addModuleAddon($company, $module, $billingCycle === 'annual' ? 'annual' : 'monthly', true);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add Module Addon to Subscription Error: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'module_id' => $module->id,
            ]);

            return false;
        }
    }

    /**
     * Remove module add-on from subscription
     */
    public function removeModuleAddonFromSubscription(Company $company, MarketplaceModule $module, bool $immediate = false): bool
    {
        return $this->moduleAddonService->removeModuleAddon($company, $module, $immediate);
    }

    /**
     * Get subscription summary
     */
    public function getSubscriptionSummary(Company $company): array
    {
        $subscription = GlobalSubscription::where('company_id', $company->id)->first();
        $package = $company->package;
        $addons = $this->moduleAddonService->getCompanyAddons($company);
        $aiCredits = $this->aiCreditsService->getOrCreateCompanyCredits($company);

        return [
            'subscription' => $subscription,
            'package' => $package,
            'addons' => $addons,
            'ai_credits' => [
                'balance' => (float) $aiCredits->balance,
                'lifetime_earned' => (float) $aiCredits->lifetime_earned,
                'lifetime_spent' => (float) $aiCredits->lifetime_spent,
            ],
        ];
    }
}

