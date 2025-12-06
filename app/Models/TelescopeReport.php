<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelescopeReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'level',
        'message',
        'context',
        'entry_uuid',
        'count',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'context' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
}

