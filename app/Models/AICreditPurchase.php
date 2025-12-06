<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\GlobalInvoice;

class AICreditPurchase extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function creditPackage(): BelongsTo
    {
        return $this->belongsTo(AICreditPackage::class, 'ai_credit_package_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(AICreditPromotion::class, 'promotion_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(GlobalInvoice::class, 'invoice_id');
    }
}
