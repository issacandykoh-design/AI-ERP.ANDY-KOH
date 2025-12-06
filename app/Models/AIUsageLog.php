<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIUsageLog extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'cost_usd' => 'decimal:4',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
