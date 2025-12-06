<?php

namespace App\Services;

use App\Models\AICreditPromotion;
use App\Models\Company;
use App\Models\AICreditPackage;
use Illuminate\Support\Facades\Log;

class AICreditPromotionService
{
    /**
     * Get active promotions
     */
    public function getActivePromotions(?Company $company = null, ?float $purchaseAmount = null): \Illuminate\Database\Eloquent\Collection
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

        if ($company && $company->package_id) {
            $query->where(function ($q) use ($company) {
                $q->whereNull('applicable_to_packages')
                    ->orWhereJsonContains('applicable_to_packages', $company->package_id);
            });
        }

        return $query->orderBy('bonus_credits_amount', 'desc')
            ->orderBy('bonus_percentage', 'desc')
            ->get();
    }

    /**
     * Get best promotion for a purchase
     */
    public function getBestPromotion(Company $company, float $purchaseAmount): ?AICreditPromotion
    {
        $promotions = $this->getActivePromotions($company, $purchaseAmount);

        if ($promotions->isEmpty()) {
            return null;
        }

        // Find promotion with highest bonus
        $bestPromotion = null;
        $maxBonus = 0;

        foreach ($promotions as $promotion) {
            $bonus = $this->calculateBonus($purchaseAmount, $promotion);

            if ($bonus > $maxBonus) {
                $maxBonus = $bonus;
                $bestPromotion = $promotion;
            }
        }

        return $bestPromotion;
    }

    /**
     * Calculate bonus credits for a promotion
     */
    public function calculateBonus(float $purchaseAmount, AICreditPromotion $promotion): float
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
     * Check if promotion can be used by company
     */
    public function canUsePromotion(Company $company, AICreditPromotion $promotion): bool
    {
        // Check package restrictions
        if ($promotion->applicable_to_packages) {
            $applicablePackages = json_decode($promotion->applicable_to_packages, true);
            if ($applicablePackages && !in_array($company->package_id, $applicablePackages)) {
                return false;
            }
        }

        // Check per-company usage limit
        if ($promotion->max_uses_per_company) {
            $usageCount = \App\Models\AICreditPurchase::where('company_id', $company->id)
                ->where('promotion_id', $promotion->id)
                ->count();

            if ($usageCount >= $promotion->max_uses_per_company) {
                return false;
            }
        }

        // Check total usage limit
        if ($promotion->total_max_uses) {
            $totalUsage = \App\Models\AICreditPurchase::where('promotion_id', $promotion->id)->count();

            if ($totalUsage >= $promotion->total_max_uses) {
                return false;
            }
        }

        return true;
    }
}

