<?php

namespace App\Services;

use App\Models\Company;
use App\Models\MarketplaceModule;
use App\Models\CompanyModuleSubscription;
use App\Models\ModuleDependency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ModuleAddonService
{
    /**
     * Add a module as add-on to company's package
     */
    public function addModuleAddon(Company $company, MarketplaceModule $module, string $subscriptionType = 'monthly', bool $autoRenew = true): ?CompanyModuleSubscription
    {
        try {
            DB::beginTransaction();

            // Check if module is already subscribed
            $existingSubscription = CompanyModuleSubscription::where('company_id', $company->id)
                ->where('marketplace_module_id', $module->id)
                ->where('status', 'active')
                ->first();

            if ($existingSubscription) {
                DB::rollBack();

                return null; // Already subscribed
            }

            // Check dependencies
            if (!$this->checkDependencies($company, $module)) {
                DB::rollBack();

                return null; // Dependencies not met
            }

            // Calculate expiration date
            $expiresAt = $this->calculateExpirationDate($subscriptionType);

            // Get price based on subscription type
            $price = $this->getPriceForSubscriptionType($module, $subscriptionType);

            // Create subscription
            $subscription = CompanyModuleSubscription::create([
                'company_id' => $company->id,
                'package_id' => $company->package_id,
                'marketplace_module_id' => $module->id,
                'subscription_type' => $subscriptionType,
                'status' => 'active',
                'subscribed_at' => now(),
                'expires_at' => $expiresAt,
                'auto_renew' => $autoRenew,
                'is_addon' => true,
            ]);

            DB::commit();

            return $subscription;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Module Addon Addition Error: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'module_id' => $module->id,
            ]);

            return null;
        }
    }

    /**
     * Remove module add-on from company
     */
    public function removeModuleAddon(Company $company, MarketplaceModule $module, bool $immediate = false): bool
    {
        try {
            DB::beginTransaction();

            $subscription = CompanyModuleSubscription::where('company_id', $company->id)
                ->where('marketplace_module_id', $module->id)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                DB::rollBack();

                return false;
            }

            if ($immediate) {
                $subscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'expires_at' => now(),
                ]);
            } else {
                // Cancel at end of billing period
                $subscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'auto_renew' => false,
                ]);
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Module Addon Removal Error: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'module_id' => $module->id,
            ]);

            return false;
        }
    }

    /**
     * Check if module dependencies are met
     */
    public function checkDependencies(Company $company, MarketplaceModule $module): bool
    {
        $dependencies = ModuleDependency::where('marketplace_module_id', $module->id)
            ->with('requiredModule')
            ->get();

        foreach ($dependencies as $dependency) {
            $requiredModule = $dependency->requiredModule;

            if (!$requiredModule) {
                continue;
            }

            // Check if required module is in package
            $hasInPackage = false;
            if ($company->package) {
                $packageModules = json_decode($company->package->module_in_package, true) ?? [];
                if (in_array($requiredModule->module_key, $packageModules)) {
                    $hasInPackage = true;
                }
            }

            // Check if required module is subscribed as add-on
            $hasAsAddon = CompanyModuleSubscription::where('company_id', $company->id)
                ->where('marketplace_module_id', $requiredModule->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->exists();

            if (!$hasInPackage && !$hasAsAddon) {
                return false; // Dependency not met
            }
        }

        return true;
    }

    /**
     * Calculate expiration date based on subscription type
     */
    protected function calculateExpirationDate(string $subscriptionType): ?Carbon
    {
        return match ($subscriptionType) {
            'monthly' => now()->addMonth(),
            'annual' => now()->addYear(),
            'lifetime' => null,
            default => now()->addMonth(),
        };
    }

    /**
     * Get price for subscription type
     */
    protected function getPriceForSubscriptionType(MarketplaceModule $module, string $subscriptionType): float
    {
        return match ($subscriptionType) {
            'monthly' => (float) ($module->price_monthly ?? 0),
            'annual' => (float) ($module->price_annual ?? 0),
            'lifetime' => (float) ($module->price_lifetime ?? 0),
            default => (float) ($module->price_monthly ?? 0),
        };
    }

    /**
     * Get company's active module add-ons
     */
    public function getCompanyAddons(Company $company): \Illuminate\Database\Eloquent\Collection
    {
        return CompanyModuleSubscription::where('company_id', $company->id)
            ->where('is_addon', true)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['marketplaceModule', 'package'])
            ->get();
    }

    /**
     * Renew module add-on subscription
     */
    public function renewAddon(CompanyModuleSubscription $subscription): bool
    {
        try {
            DB::beginTransaction();

            if ($subscription->subscription_type === 'lifetime') {
                DB::rollBack();

                return false; // Lifetime subscriptions don't need renewal
            }

            $newExpiresAt = $this->calculateExpirationDate($subscription->subscription_type);

            $subscription->update([
                'expires_at' => $newExpiresAt,
                'status' => 'active',
                'cancelled_at' => null,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Module Addon Renewal Error: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id,
            ]);

            return false;
        }
    }

    /**
     * Check and expire outdated subscriptions
     */
    public function expireOutdatedSubscriptions(): int
    {
        $expired = CompanyModuleSubscription::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
            ]);

        return $expired;
    }
}

