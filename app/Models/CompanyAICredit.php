<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyAICredit extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AICreditTransaction::class, 'company_id', 'company_id');
    }
}
