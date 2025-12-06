<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SuperAdmin\Package;

class CompanyModuleSubscription extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'is_addon' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function marketplaceModule(): BelongsTo
    {
        return $this->belongsTo(MarketplaceModule::class, 'marketplace_module_id');
    }

    public function subscriptionItems(): HasMany
    {
        return $this->hasMany(ModuleSubscriptionItem::class, 'company_module_subscription_id');
    }
}
