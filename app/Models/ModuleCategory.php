<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleCategory extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ModuleCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ModuleCategory::class, 'parent_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(MarketplaceModule::class, 'category_id');
    }
}
