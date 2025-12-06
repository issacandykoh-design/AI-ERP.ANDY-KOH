<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AICreditPromotion extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'applicable_to_packages' => 'array',
        'bonus_percentage' => 'decimal:2',
        'minimum_purchase_amount' => 'decimal:2',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(AICreditPurchase::class, 'promotion_id');
    }

    public function isActive(): bool
    {
        return $this->is_active
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    public function calculateBonus(int $purchaseAmount): int
    {
        if ($this->bonus_credits_amount) {
            return $this->bonus_credits_amount;
        }

        if ($this->bonus_percentage) {
            return (int) ($purchaseAmount * ($this->bonus_percentage / 100));
        }

        return 0;
    }
}
