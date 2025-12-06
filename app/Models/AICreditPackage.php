<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AICreditPackage extends BaseModel
{
    use HasFactory;

    protected $table = 'ai_credit_packages';

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(AICreditPurchase::class, 'ai_credit_package_id');
    }
}
