<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModuleSwitch extends BaseModel
{
    use HasFactory;

    protected $table = 'module_switches';

    protected $fillable = [
        'module_name',
        'display_name',
        'description',
        'version',
        'is_enabled'
    ];

    protected $casts = [
        'is_enabled' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get modules that are enabled
     */
    public static function enabledModules()
    {
        return static::where('is_enabled', true)->get();
    }

    /**
     * Get modules that are disabled
     */
    public static function disabledModules()
    {
        return static::where('is_enabled', false)->get();
    }

    /**
     * Check if a specific module is enabled
     */
    public static function isModuleEnabled($moduleName)
    {
        $module = static::where('module_name', $moduleName)->first();
        return $module ? $module->is_enabled : false;
    }

    /**
     * Enable a module
     */
    public static function enableModule($moduleName)
    {
        return static::where('module_name', $moduleName)->update(['is_enabled' => true]);
    }

    /**
     * Disable a module
     */
    public static function disableModule($moduleName)
    {
        return static::where('module_name', $moduleName)->update(['is_enabled' => false]);
    }

    /**
     * Get module status badge
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_enabled 
            ? '<span class="badge badge-success">' . __('app.enabled') . '</span>'
            : '<span class="badge badge-danger">' . __('app.disabled') . '</span>';
    }

    /**
     * Get module status text
     */
    public function getStatusTextAttribute()
    {
        return $this->is_enabled ? __('app.enabled') : __('app.disabled');
    }
}