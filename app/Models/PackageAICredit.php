<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageAICredit extends BaseModel
{
    use HasFactory;

    protected $table = 'package_ai_credits';

    protected $guarded = ['id'];
}
