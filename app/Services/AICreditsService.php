<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyAICredit;
use App\Models\AICreditPackage;
use App\Models\AICreditPurchase;
use App\Models\AICreditTransaction;
use App\Models\AICreditPromotion;
use App\Models\AICreditExhaustionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AICreditsService
{
    /**
     * Get or create AI credits record for a company
     */
    public function getOrCreateCompanyCredits(Company $company): CompanyAICredit
    {
        return CompanyAICredit::firstOrCreate(
            ['company_id' => $company->id],
            [
                'balance' => 0,
                'lifetime_earned' => 0,
                'lifetime_spent' => 0,
                'monthly_credits_from_package' => 0,
            ]
        );
    }

    /**
     * Get current credit balance for a company
     */
    public function getBalance(Company $company): float
    {
        $credits = $this->getOrCreateCompanyCredits($company);

        return (float) $credits->balance;
    }

    /**
     * Check if company has sufficient credits
     */
    public function hasSufficientCredits(Company $company, float $requiredCredits): bool
    {
        $balance = $this->getBalance($company);

        return $balance >= $requiredCredits;
    }

    /**
     * Deduct credits from company balance
     */
    public function deductCredits(Company $company, float $credits, string $description = '', ?string $referenceType = null, ?int $referenceId = null): bool
    {
        if ($credits <= 0) {
            return true;
        }

        try {
            DB::beginTransaction();

            $companyCredits = $this->getOrCreateCompanyCredits($company);
            $oldBalance = (float) $companyCredits->balance;

            if ($oldBalance < $credits) {
                // Log exhaustion attempt
                $this->logExhaustionAttempt($company, $credits, $oldBalance, $description);

                DB::rollBack();

                return false;
            }

            $newBalance = $oldBalance - $credits;

            // Update balance
            $companyCredits->update([
                'balance' => $newBalance,
                'lifetime_spent' => DB::raw('lifetime_spent + ' . $credits),
                'last_updated_at' => now(),
            ]);

            // Create transaction record
            AICreditTransaction::create([
                'company_id' => $company->id,
                'transaction_type' => 'usage',
                'amount' => -$credits,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI Credits Deduction Error: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'credits' => $credits,
            ]);

            return false;
        }
    }

    /**
     * Add credits to company balance
     */
    public function addCredits(Company $company, float $credits, string $description = '', string $transactionType = 'purchase', ?string $referenceType = null, ?int $referenceId = null): bool
    {
        if ($credits <= 0) {
            return false;
        }

        try {
            DB::beginTransaction();

            $companyCredits = $this->getOrCreateCompanyCredits($company);
            $oldBalance = (float) $companyCredits->balance;
            $newBalance = $oldBalance + $credits;

            // Update balance
            $companyCredits->update([
                'balance' => $newBalance,
                'lifetime_earned' => DB::raw('lifetime_earned + ' . $credits),
                'last_updated_at' => now(),
            ]);

            // Create transaction record
            AICreditTransaction::create([
                'company_id' => $company->id,
                'transaction_type' => $transactionType,
                'amount' => $credits,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI Credits Addition Error: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'credits' => $credits,
            ]);

            return false;
        }
    }

    /**
     * Purchase AI credits
     */
    public function purchaseCredits(Company $company, AICreditPackage $package, ?AICreditPromotion $promotion = null, ?int $invoiceId = null): ?AICreditPurchase
    {
        try {
            DB::beginTransaction();

            $baseCredits = (float) $package->credits_amount;
            $bonusCredits = 0;

            // Apply promotion if provided
            if ($promotion && $this->isPromotionEligible($company, $promotion, $package->price)) {
                $bonusCredits = $this->calculateBonusCredits($package->price, $promotion);
            }

            $totalCredits = $baseCredits + $bonusCredits;

            // Create purchase record
            $purchase = AICreditPurchase::create([
                'company_id' => $company->id,
                'ai_credit_package_id' => $package->id,
                'promotion_id' => $promotion?->id,
                'credits_amount' => $baseCredits,
                'bonus_credits_amount' => $bonusCredits,
                'price' => $package->price,
                'currency_id' => $package->currency_id,
                'status' => 'completed',
                'purchased_at' => now(),
                'invoice_id' => $invoiceId,
            ]);

            // Add credits to company balance
            $this->addCredits(
                $company,
                $totalCredits,
                "Purchased {$baseCredits} credits" . ($bonusCredits > 0 ? " + {$bonusCredits} bonus" : ''),
                'purchase',
                'purchase_id',
                $purchase->id
            );

            // Update promotion usage if applicable
            if ($promotion) {
                $this->recordPromotionUsage($company, $promotion);
            }

            DB::commit();

            return $purchase;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI Credits Purchase Error: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'package_id' => $package->id,
            ]);

            return null;
        }
    }

    /**
     * Check if promotion is eligible
     */
    public function isPromotionEligible(Company $company, AICreditPromotion $promotion, float $purchaseAmount): bool
    {
        if (!$promotion->is_active) {
            return false;
        }

        $now = now();
        if ($promotion->start_date && $now->lt($promotion->start_date)) {
            return false;
        }

        if ($promotion->end_date && $now->gt($promotion->end_date)) {
            return false;
        }

        if ($promotion->minimum_purchase_amount && $purchaseAmount < $promotion->minimum_purchase_amount) {
            return false;
        }

        // Check package restrictions
        if ($promotion->applicable_to_packages) {
            $applicablePackages = json_decode($promotion->applicable_to_packages, true);
            if ($applicablePackages && !in_array($company->package_id, $applicablePackages)) {
                return false;
            }
        }

        // Check usage limits
        if ($promotion->max_uses_per_company) {
            $usageCount = AICreditPurchase::where('company_id', $company->id)
                ->where('promotion_id', $promotion->id)
                ->count();

            if ($usageCount >= $promotion->max_uses_per_company) {
                return false;
            }
        }

        if ($promotion->total_max_uses) {
            $totalUsage = AICreditPurchase::where('promotion_id', $promotion->id)->count();

            if ($totalUsage >= $promotion->total_max_uses) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate bonus credits from promotion
     */
    public function calculateBonusCredits(float $purchaseAmount, AICreditPromotion $promotion): float
    {
        if ($promotion->bonus_credits_amount) {
            return (float) $promotion->bonus_credits_amount;
        }

        if ($promotion->bonus_percentage) {
            return round($purchaseAmount * ($promotion->bonus_percentage / 100), 2);
        }

        return 0;
    }

    /**
     * Record promotion usage
     */
    protected function recordPromotionUsage(Company $company, AICreditPromotion $promotion): void
    {
        // Promotion usage is tracked via AICreditPurchase records
        // This method can be extended for additional tracking if needed
    }

    /**
     * Log credit exhaustion attempt
     */
    protected function logExhaustionAttempt(Company $company, float $requiredCredits, float $currentBalance, string $description): void
    {
        AICreditExhaustionLog::create([
            'company_id' => $company->id,
            'required_credits' => $requiredCredits,
            'available_credits' => $currentBalance,
            'shortfall' => $requiredCredits - $currentBalance,
            'description' => $description,
        ]);
    }

    /**
     * Get available promotions for a company
     */
    public function getAvailablePromotions(Company $company, ?float $purchaseAmount = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = AICreditPromotion::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });

        if ($purchaseAmount) {
            $query->where(function ($q) use ($purchaseAmount) {
                $q->whereNull('minimum_purchase_amount')
                    ->orWhere('minimum_purchase_amount', '<=', $purchaseAmount);
            });
        }

        return $query->orderBy('bonus_credits_amount', 'desc')
            ->orderBy('bonus_percentage', 'desc')
            ->get();
    }

    /**
     * Get transaction history for a company
     */
    public function getTransactionHistory(Company $company, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AICreditTransaction::where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

