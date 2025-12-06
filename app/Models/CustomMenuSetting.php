<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomMenuSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'menu_key',
        'custom_name',
        'menu_order',
        'is_visible',
        'locale',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'menu_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get custom menu name for a menu key, or return default
     */
    public static function getCustomName(int $companyId, string $menuKey, string $defaultName, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $cacheKey = "menu_custom_name_{$companyId}_{$menuKey}_{$locale}";
        
        return \Cache::remember($cacheKey, 3600, function () use ($companyId, $menuKey, $defaultName, $locale) {
            $setting = self::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('menu_key', $menuKey)
                ->where('locale', $locale)
                ->first();

            if ($setting && $setting->custom_name) {
                return $setting->custom_name;
            }

            $generic = self::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('menu_key', $menuKey)
                ->whereNull('locale')
                ->first();

            if ($generic && $generic->custom_name) {
                return $generic->custom_name;
            }

            return $defaultName;
        });
    }

    /**
     * Get menu order for a menu key
     */
    public static function getMenuOrder(int $companyId, string $menuKey): int
    {
        $cacheKey = "menu_order_{$companyId}_{$menuKey}";
        
        return \Cache::remember($cacheKey, 3600, function () use ($companyId, $menuKey) {
            $setting = self::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('menu_key', $menuKey)
                ->first();

            // Return the saved order, or 999 if not set (defaults to end of list)
            if ($setting && $setting->menu_order > 0 && $setting->menu_order < 999) {
                return $setting->menu_order;
            }

            $globalOrder = self::getGlobalDefaultOrderFor($menuKey);
            return $globalOrder > 0 && $globalOrder < 999 ? $globalOrder : 999;
        });
    }

    /**
     * Check if menu is visible
     */
    public static function isMenuVisible(int $companyId, string $menuKey): bool
    {
        $cacheKey = "menu_visible_{$companyId}_{$menuKey}";
        
        return \Cache::remember($cacheKey, 3600, function () use ($companyId, $menuKey) {
            $setting = self::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('menu_key', $menuKey)
                ->first();

            return $setting ? $setting->is_visible : true;
        });
    }

    public static function getGlobalDefaultOrderFor(string $menuKey): int
    {
        $defaults = [
            'aipro', 'dashboard', 'noticeboard',
            'orders', 'purchase', 'lead', 'clients',
            'finance', 'reports',
            'work', 'mycalendar', 'events', 'messages', 'letter',
            'hr', 'payroll', 'performance', 'knowledgebase', 'recruit', 'asset',
            'settings', 'help', 'tickets',
            'servermanager', 'gdpr', 'qrcode', 'biolinks', 'biometric', 'webhooks', 'zoom'
        ];

        $lookupKey = strtolower($menuKey);
        $index = array_search($lookupKey, $defaults, true);
        if ($index === false) {
            return 999;
        }
        $pos = $index + 1;
        return ($pos > 0 && $pos < 999) ? $pos : 999;
    }

    public static function getGlobalDefaultKeys(): array
    {
        return [
            'aipro', 'dashboard', 'noticeboard',
            'orders', 'purchase', 'lead', 'clients',
            'finance', 'reports',
            'work', 'mycalendar', 'events', 'messages', 'letter',
            'hr', 'payroll', 'performance', 'knowledgebase', 'recruit', 'asset',
            'settings', 'help', 'tickets',
            'servermanager', 'gdpr', 'qrcode', 'biolinks', 'biometric', 'webhooks', 'zoom'
        ];
    }

    /**
     * Clear cache for a specific company and menu key
     */
    public static function clearMenuCache(int $companyId, ?string $menuKey = null): void
    {
        if ($menuKey) {
            // Clear all locale variants
            foreach (array_merge([app()->getLocale()], config('app.supported_locales', [])) as $loc) {
                \Cache::forget("menu_custom_name_{$companyId}_{$menuKey}_{$loc}");
            }
            \Cache::forget("menu_order_{$companyId}_{$menuKey}");
            \Cache::forget("menu_visible_{$companyId}_{$menuKey}");
        } else {
            // Clear all menu caches for this company
            $settings = self::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->get();
            
            foreach ($settings as $setting) {
                foreach (array_merge([app()->getLocale()], config('app.supported_locales', [])) as $loc) {
                    \Cache::forget("menu_custom_name_{$companyId}_{$setting->menu_key}_{$loc}");
                }
                \Cache::forget("menu_order_{$companyId}_{$setting->menu_key}");
                \Cache::forget("menu_visible_{$companyId}_{$setting->menu_key}");
            }
        }
    }
}

