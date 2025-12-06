<?php

namespace App\Http\Controllers\SuperAdmin;

use App\DataTables\SuperAdmin\ModuleSwitchDataTable;
use App\Http\Controllers\AccountBaseController;
use App\Http\Requests\SuperAdmin\ModuleSwitch\UpdateModuleSwitchRequest;
use App\Models\ModuleSwitch;
use App\Helper\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ModuleSwitchController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->activeSettingMenu = 'module_switch';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_superadmin_module_switch') !== 'all');
            return $next($request);
        });
    }

    /**
     * Display the module switch settings page
     */
    public function index(ModuleSwitchDataTable $dataTable)
    {
        $this->pageTitle = __('app.menu.moduleSwitch');
        $this->view = 'superadmin.settings.module-switch.index';

        // Sync modules from the Modules directory with database
        $this->syncModulesWithDatabase();

        if (request()->ajax()) {
            return $dataTable->ajax();
        }

        $this->dataTable = $dataTable->html();
        return view('superadmin.settings.module-switch.index', $this->data);
    }

    /**
     * Sync modules from filesystem with database
     */
    private function syncModulesWithDatabase()
    {
        $modulesPath = base_path('Modules');

        if (File::exists($modulesPath)) {
            $moduleDirectories = File::directories($modulesPath);
            
            foreach ($moduleDirectories as $moduleDir) {
                $moduleName = basename($moduleDir);
                $moduleJsonPath = $moduleDir . '/module.json';
                
                if (File::exists($moduleJsonPath)) {
                    $moduleConfig = json_decode(File::get($moduleJsonPath), true);
                    
                    // Get or create module switch record
                    ModuleSwitch::firstOrCreate(
                        ['module_name' => $moduleName],
                        [
                            'module_name' => $moduleName,
                            'is_enabled' => $moduleConfig['active'] ?? true,
                            'display_name' => $moduleConfig['name'] ?? $moduleName,
                            'description' => $moduleConfig['description'] ?? '',
                            'version' => $moduleConfig['version'] ?? '1.0.0'
                        ]
                    );
                }
            }
        }
    }

    /**
     * Toggle module status
     */
    public function toggleStatus(Request $request)
    {
        $moduleSwitch = ModuleSwitch::findOrFail($request->id);
        $moduleSwitch->is_enabled = !$moduleSwitch->is_enabled;
        $moduleSwitch->save();

        // Update the module.json file
        $modulePath = base_path('Modules/' . $moduleSwitch->module_name . '/module.json');
        if (File::exists($modulePath)) {
            $moduleConfig = json_decode(File::get($modulePath), true);
            $moduleConfig['active'] = $moduleSwitch->is_enabled;
            File::put($modulePath, json_encode($moduleConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Clear user modules cache for all users to reflect the change immediately
        $this->clearUserModulesCache();

        $status = $moduleSwitch->is_enabled ? __('app.enabled') : __('app.disabled');
        
        return Reply::success(__('messages.moduleStatusUpdated', ['module' => $moduleSwitch->display_name, 'status' => $status]));
    }

    /**
     * Bulk toggle modules
     */
    public function bulkToggle(Request $request)
    {
        $action = $request->action; // 'enable_all' or 'disable_all'
        $isEnabled = $action === 'enable_all';

        $modules = ModuleSwitch::all();
        
        foreach ($modules as $moduleSwitch) {
            $moduleSwitch->is_enabled = $isEnabled;
            $moduleSwitch->save();

            // Update the module.json file
            $modulePath = base_path('Modules/' . $moduleSwitch->module_name . '/module.json');
            if (File::exists($modulePath)) {
                $moduleConfig = json_decode(File::get($modulePath), true);
                $moduleConfig['active'] = $isEnabled;
                File::put($modulePath, json_encode($moduleConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        }

        // Clear user modules cache for all users to reflect the change immediately
        $this->clearUserModulesCache();

        $message = $isEnabled ? __('messages.allModulesEnabled') : __('messages.allModulesDisabled');
        
        return Reply::success($message);
    }

    /**
     * Get module details
     */
    public function show($id)
    {
        $moduleSwitch = ModuleSwitch::findOrFail($id);
        $modulePath = base_path('Modules/' . $moduleSwitch->module_name);
        
        $moduleDetails = [
            'name' => $moduleSwitch->module_name,
            'display_name' => $moduleSwitch->display_name,
            'description' => $moduleSwitch->description,
            'version' => $moduleSwitch->version,
            'is_enabled' => $moduleSwitch->is_enabled,
            'path' => $modulePath,
            'size' => $this->getDirectorySize($modulePath)
        ];

        // Get module.json details
        $moduleJsonPath = $modulePath . '/module.json';
        if (File::exists($moduleJsonPath)) {
            $moduleConfig = json_decode(File::get($moduleJsonPath), true);
            $moduleDetails = array_merge($moduleDetails, $moduleConfig);
        }

        return Reply::successWithData(__('messages.success'), ['module' => $moduleDetails]);
    }

    /**
     * Calculate directory size
     */
    private function getDirectorySize($path)
    {
        if (!File::exists($path)) {
            return 0;
        }

        $size = 0;
        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $this->formatBytes($size);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Clear user modules cache for all users
     */
    private function clearUserModulesCache()
    {
        // Get all users and clear their module cache
        $users = \App\Models\User::select('id')->get();
        
        foreach ($users as $user) {
            cache()->forget('user_modules_' . $user->id);
        }
        
        // Also clear the craveva_plugins cache
        cache()->forget('craveva_plugins');
    }
}