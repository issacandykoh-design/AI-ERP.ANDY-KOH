<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceModule extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tags' => 'array',
        'screenshots' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'rating' => 'decimal:2',
        'price_monthly' => 'decimal:2',
        'price_annual' => 'decimal:2',
        'price_lifetime' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ModuleCategory::class, 'category_id');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(ModuleDependency::class, 'marketplace_module_id');
    }

    public function requiredModules(): HasMany
    {
        return $this->hasMany(ModuleDependency::class, 'required_module_id');
    }

    public function companySubscriptions(): HasMany
    {
        return $this->hasMany(CompanyModuleSubscription::class, 'marketplace_module_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ModulePurchase::class, 'marketplace_module_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ModuleReview::class, 'marketplace_module_id');
    }
}
