<?php

namespace Open\RestAPI;

use Illuminate\Database\Eloquent\Model;

class ApiModel extends Model
{
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Convert the model instance to an array for API response.
     */
    public function toApiArray(): array
    {
        return $this->toArray();
    }



    /**
     * Get formatted created date.
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * Get formatted updated date.
     */
    public function getFormattedUpdatedAtAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '';
    }
}
