<?php

namespace App\Http\Controllers;

use App\Helper\MenuDiscovery;
use App\Helper\Reply;
use App\Models\CustomMenuSetting;
use App\Models\ModuleSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class MenuCustomizationController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.menuCustomization';
        $this->activeSettingMenu = 'menu_customization';
        $this->middleware(function ($request, $next) {
            if (user()->is_superadmin) {
                return $next($request);
            }

            $hasExplicitPerm = user()->permission('manage_menu_customization') == 'all';
            $isAdmin = in_array('admin', user_roles());

            $inPackage = false;
            if (company() && company()->package && company()->package->module_in_package) {
                $mods = json_decode(company()->package->module_in_package, true) ?: [];
                $inPackage = in_array('menu_customization', $mods);
            }

            $moduleEnabled = ModuleSetting::checkModule('menu_customization');

            abort_403(!((($hasExplicitPerm || $isAdmin) && ($inPackage || $moduleEnabled))));

            return $next($request);
        });
    }

    public function index()
    {
        $companyId = user()->company_id;

        // Dynamically discover all menu items from sidebar templates
        // This automatically finds all menu items without hardcoding
        $menuItems = MenuDiscovery::discoverMenuItems();

        // Get existing custom settings (without global scope to ensure we get all records)
        $customSettings = collect();
        if (Schema::hasTable('custom_menu_settings')) {
            $customSettingsLocale = CustomMenuSetting::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('locale', app()->getLocale())
                ->get()
                ->keyBy('menu_key');

            $customSettingsGeneric = CustomMenuSetting::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->whereNull('locale')
                ->get()
                ->keyBy('menu_key');

            $customSettings = $customSettingsLocale->union($customSettingsGeneric);
        }

        // Merge with custom settings and initialize default orders if needed
        $defaultOrder = 1;
        foreach ($menuItems as &$item) {
            if ($customSettings->has($item['key'])) {
                $setting = $customSettings[$item['key']];
                $item['custom_name'] = $setting->custom_name;
                // Use saved order, or default order if order is 999 (not set)
                $item['menu_order'] = ($setting->menu_order == 999 || $setting->menu_order == 0) ? $defaultOrder : $setting->menu_order;
                $item['id'] = $setting->id;
            } else {
                $item['custom_name'] = null;
                $item['menu_order'] = $defaultOrder;
                $item['id'] = null;
            }
            $defaultOrder++;
        }

        // Remove duplicates by menu_key (in case of any duplicates)
        $uniqueMenuItems = [];
        $seenKeys = [];
        foreach ($menuItems as $item) {
            if (! in_array($item['key'], $seenKeys)) {
                $uniqueMenuItems[] = $item;
                $seenKeys[] = $item['key'];
            }
        }
        
        // Sort by menu_order
        usort($uniqueMenuItems, function ($a, $b) {
            return $a['menu_order'] <=> $b['menu_order'];
        });

        $this->menuItems = $uniqueMenuItems;

        if (request()->ajax()) {
            $html = view('menu-customization.ajax.menu-items', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('menu-customization.index', $this->data);
    }

    /**
     * Save all menu customizations at once (batch save)
     */
    public function saveAll(Request $request)
    {
        $companyId = user()->company_id;
        $menuItems = $request->menu_items; // Array of [menu_key => [custom_name, menu_order, is_visible]]

        if (! is_array($menuItems)) {
            return Reply::error(__('messages.invalidRequest'));
        }

        if (! Schema::hasTable('custom_menu_settings')) {
            return Reply::error(__('messages.invalidRequest'));
        }

        $locale = app()->getLocale();

        \DB::transaction(function () use ($companyId, $menuItems, $locale) {
            foreach ($menuItems as $menuKey => $data) {
                $customName = $data['custom_name'] ?? null;
                $menuOrder = isset($data['menu_order']) ? (int) $data['menu_order'] : 999;

                if ($menuOrder < 1 || $menuOrder > 998) {
                    $menuOrder = 999;
                }

                $existingAny = CustomMenuSetting::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->where('menu_key', $menuKey)
                    ->first();

                if ($existingAny) {
                    $existingAny->custom_name = $customName ?: null;
                    $existingAny->menu_order = $menuOrder;
                    $existingAny->is_visible = $existingAny->is_visible ?? true;
                    $existingAny->locale = $existingAny->locale ?: $locale;
                    $existingAny->save();
                } else {
                    CustomMenuSetting::withoutGlobalScopes()->create([
                        'company_id' => $companyId,
                        'menu_key' => $menuKey,
                        'locale' => $locale,
                        'custom_name' => $customName ?: null,
                        'menu_order' => $menuOrder,
                        'is_visible' => true,
                    ]);
                }
            }
        });

        // Clear all menu order caches for this company first
        $menuKeys = array_keys($menuItems);
        foreach ($menuKeys as $menuKey) {
            \Cache::forget("menu_order_{$companyId}_{$menuKey}");
            \Cache::forget("menu_custom_name_{$companyId}_{$menuKey}");
            \Cache::forget("menu_visible_{$companyId}_{$menuKey}");
        }

        // Clear cache to ensure changes are reflected immediately
        $this->clearMenuCache($companyId);
        
        // Also clear view cache to force re-rendering of menu items with new order
        \Artisan::call('view:clear');
        \Cache::forget('menu_customization_' . $companyId);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @deprecated Use saveAll instead
     */
    public function update(Request $request)
    {
        return $this->saveAll($request);
    }

    public function reorder(Request $request)
    {
        $companyId = user()->company_id;
        $menuOrder = $request->menu_order; // Array of menu_key => order

        if (! is_array($menuOrder) || empty($menuOrder)) {
            return Reply::error(__('messages.invalidRequest'));
        }

        if (! Schema::hasTable('custom_menu_settings')) {
            return Reply::error(__('messages.invalidRequest'));
        }

        // Use database transaction to ensure all updates succeed or fail together
        \DB::transaction(function () use ($companyId, $menuOrder) {
            foreach ($menuOrder as $menuKey => $order) {
                // Ensure order is a valid integer
                $orderValue = (int) $order;
                if ($orderValue < 1) {
                    $orderValue = 999; // Default to end if invalid
                }

                CustomMenuSetting::withoutGlobalScopes()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'menu_key' => $menuKey,
                    ],
                    [
                        'menu_order' => $orderValue,
                    ]
                );
            }
        });

        // Clear cache to ensure changes are reflected immediately
        $this->clearMenuCache($companyId);
        \Cache::store('file')->put('menu_progress_' . $companyId, [
            'phase' => 'pitch',
            'started_at' => now()->timestamp,
            'expected_secs' => 30,
        ], 60);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Report backend progress of reset + pitch generation
     */
    public function progress(Request $request)
    {
        $companyId = user()->company_id;
        $state = \Cache::store('file')->get('menu_progress_' . $companyId);

        if (! $state) {
            return response()->json(['percent' => 30, 'phase' => 'pitch', 'eta_secs' => 25]);
        }

        $started = (int) ($state['started_at'] ?? now()->timestamp);
        $expected = (int) ($state['expected_secs'] ?? 25);
        if ($expected < 1) { $expected = 25; }

        $elapsed = max(0, now()->timestamp - $started);
        $percentTime = (int) floor(($elapsed / $expected) * 100);
        $percentFromState = isset($state['percent']) ? (int) $state['percent'] : null;
        $percent = $percentFromState !== null ? max($percentFromState, $percentTime) : $percentTime;
        $percent = max(0, min(100, $percent));

        if ($percent >= 100) {
            \Cache::store('file')->forget('menu_progress_' . $companyId);
        }

        return response()->json([
            'percent' => $percent,
            'phase' => $state['phase'] ?? 'pitch',
            'eta_secs' => max(0, $expected - $elapsed),
        ]);
    }

    public function toggleVisibility(Request $request)
    {
        $companyId = user()->company_id;
        $menuKey = $request->menu_key;
        $isVisible = $request->is_visible;

        if (! Schema::hasTable('custom_menu_settings')) {
            return Reply::error(__('messages.invalidRequest'));
        }

        // Get existing to preserve custom_name and menu_order
        $existing = CustomMenuSetting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('menu_key', $menuKey)
            ->first();

        if ($existing) {
            $existing->is_visible = (bool) $isVisible;
            $existing->save();
            $setting = $existing;
        } else {
            $setting = CustomMenuSetting::withoutGlobalScopes()->create([
                'company_id' => $companyId,
                'menu_key' => $menuKey,
                'is_visible' => (bool) $isVisible,
                'menu_order' => 999,
            ]);
        }

        // Clear cache to ensure changes are reflected immediately
        $this->clearMenuCache($companyId);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Clear all caches related to menu customization
     */
    private function clearMenuCache(int $companyId): void
    {
        // Clear menu-specific caches
        \App\Models\CustomMenuSetting::clearMenuCache($companyId);

        // Clear user modules cache for all users in the company
        $users = \App\Models\User::where('company_id', $companyId)->pluck('id');
        foreach ($users as $userId) {
            \Cache::forget('user_modules_'.$userId);
            \Cache::forget('sidebar_user_perms_'.$userId);
        }

        // Heavy clears removed for performance during reset flow
    }

    private function clearMenuCacheLight(int $companyId): void
    {
        \App\Models\CustomMenuSetting::clearMenuCache($companyId);
        $users = \App\Models\User::where('company_id', $companyId)->pluck('id');
        foreach ($users as $userId) {
            \Cache::forget('user_modules_'.$userId);
            \Cache::forget('sidebar_user_perms_'.$userId);
        }
    }

    /**
     * Reset all menu visibility to visible (for fixing issues)
     */
    public function resetVisibility(Request $request)
    {
        $companyId = user()->company_id;

        if (! Schema::hasTable('custom_menu_settings')) {
            return Reply::error(__('messages.invalidRequest'));
        }

        // Set all menu items to visible
        CustomMenuSetting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->update(['is_visible' => true]);

        // Clear cache
        $this->clearMenuCacheLight($companyId);

        return Reply::success(__('messages.updateSuccess'));
    }

    public function resetAll(Request $request)
    {
        $companyId = user()->company_id;

        if (! Schema::hasTable('custom_menu_settings')) {
            return Reply::error(__('messages.invalidRequest'));
        }

        // Initialize progress state for reset phase (time-based)
        \Cache::store('file')->put('menu_progress_' . $companyId, [
            'phase' => 'reset',
            'started_at' => now()->timestamp,
            'expected_secs' => 5,
        ], 60);

        \DB::transaction(function () use ($companyId) {
            // Discover all menu keys to guarantee records exist
            $discovered = MenuDiscovery::discoverMenuItems();
            foreach ($discovered as $item) {
                $menuKey = $item['key'];
                $defaultOrder = \App\Models\CustomMenuSetting::getGlobalDefaultOrderFor($menuKey);

                CustomMenuSetting::withoutGlobalScopes()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'menu_key' => $menuKey,
                    ],
                    [
                        'custom_name' => null,
                        'menu_order' => $defaultOrder,
                        'is_visible' => true,
                        'locale' => null,
                    ]
                );
            }
        });

        $this->clearMenuCache($companyId);

        // Switch to pitch phase (time-based; generator may override percent)
        \Cache::store('file')->put('menu_progress_' . $companyId, [
            'phase' => 'pitch',
            'started_at' => now()->timestamp,
            'expected_secs' => 30,
        ], 60);

        return Reply::success(__('messages.updateSuccess'));
    }

    public function applyGlobalDefaultAll(Request $request)
    {
        abort_403(!user()->is_superadmin);

        $keys = \App\Models\CustomMenuSetting::getGlobalDefaultKeys();
        $companyIds = \App\Models\Company::pluck('id');

        \DB::transaction(function () use ($companyIds, $keys) {
            foreach ($companyIds as $companyId) {
                foreach ($keys as $menuKey) {
                    $defaultOrder = \App\Models\CustomMenuSetting::getGlobalDefaultOrderFor($menuKey);
                    \App\Models\CustomMenuSetting::withoutGlobalScopes()->updateOrCreate(
                        [
                            'company_id' => $companyId,
                            'menu_key' => $menuKey,
                        ],
                        [
                            'custom_name' => null,
                            'menu_order' => $defaultOrder,
                            'is_visible' => true,
                            'locale' => null,
                        ]
                    );
                }
                \App\Models\CustomMenuSetting::clearMenuCache($companyId);
            }
        });

        return Reply::success(__('messages.updateSuccess'));
    }
}
