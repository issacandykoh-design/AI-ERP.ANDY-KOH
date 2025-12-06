<?php

namespace App\Console\Commands;

use App\Helper\MenuDiscovery;
use App\Models\Company;
use App\Models\SuperAdmin\Package;
use Illuminate\Console\Command;

class DiagnoseMenuDiscovery extends Command
{
    protected $signature = 'menu:diagnose {--company=1 : Company ID to diagnose}';
    protected $description = 'Diagnose menu discovery issues - compare package modules with discovered menu items';

    public function handle()
    {
        $companyId = $this->option('company');
        $company = Company::find($companyId);

        if (!$company) {
            $this->error("Company ID {$companyId} not found");
            return Command::FAILURE;
        }

        $this->info("=== DIAGNOSING MENU DISCOVERY FOR COMPANY {$companyId} ===\n");

        // Compute enabled modules directly (simulating what user_modules() would return for admin role)
        // This avoids needing to authenticate a user in console context

        // Get package info
        $package = Package::find($company->package_id);
        if (!$package) {
            $this->error("Package not found for company");
            return Command::FAILURE;
        }

        $modulesInPackage = json_decode($package->module_in_package, true);
        $modulesInPackage = array_values($modulesInPackage); // Remove keys, just get values
        $modulesInPackage = array_map('strtolower', $modulesInPackage);

        $this->info("Package: {$package->name}");
        $this->info("Modules in Package: " . count($modulesInPackage));
        $this->line("  " . implode(', ', $modulesInPackage));
        $this->newLine();

        // Get ModuleSetting records
        $moduleSettings = \App\Models\ModuleSetting::where('company_id', $companyId)
            ->where('is_allowed', 1)
            ->where('status', 'active')
            ->get();

        $activeModules = $moduleSettings->pluck('module_name')->unique()->map('strtolower')->sort()->values()->toArray();
        $this->info("Active ModuleSettings: " . count($activeModules));
        $this->line("  " . implode(', ', $activeModules));
        $this->newLine();

        // Get ModuleSwitch
        $moduleSwitches = \App\Models\ModuleSwitch::where('is_enabled', 1)->get();
        $enabledSwitches = $moduleSwitches->pluck('module_name')->map('strtolower')->sort()->values()->toArray();
        $this->info("Enabled ModuleSwitches: " . count($enabledSwitches));
        $this->line("  " . implode(', ', $enabledSwitches));
        $this->newLine();

        // Compute what user_modules() would return for admin role
        // This is: ModuleSetting (is_allowed=1, status='active', type='admin') AND ModuleSwitch (is_enabled=1)
        $adminModuleSettings = \App\Models\ModuleSetting::where('company_id', $companyId)
            ->where('is_allowed', 1)
            ->where('status', 'active')
            ->where('type', 'admin')
            ->pluck('module_name')
            ->map('strtolower')
            ->toArray();

        // Filter by ModuleSwitch
        $actualUserModules = [];
        foreach ($adminModuleSettings as $moduleName) {
            $moduleSwitch = \App\Models\ModuleSwitch::where('module_name', $moduleName)->first();
            if (!$moduleSwitch || $moduleSwitch->is_enabled) {
                $actualUserModules[] = $moduleName;
            }
        }
        sort($actualUserModules);
        $this->info("Computed user_modules() result (admin role):");
        $this->line("  " . implode(', ', $actualUserModules));
        $this->newLine();

        // Get discovered menu items (pass computed modules to avoid needing user context)
        $this->info("MenuDiscovery Results:");
        try {
            $menuItems = MenuDiscovery::discoverMenuItems($actualUserModules);
            $menuKeys = array_column($menuItems, 'key');
            $menuKeys = array_map('strtolower', $menuKeys);
            sort($menuKeys);
            $this->info("  Found: " . count($menuItems) . " menu items");
            $this->line("  " . implode(', ', $menuKeys));
            $this->newLine();
        } catch (\Exception $e) {
            $this->error("Error discovering menu items: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }

        // Compare
        $this->info("=== COMPARISON ===");
        
        // Use actual user_modules() for comparison
        $modulesToCheck = $actualUserModules;
        
        // Modules in package but not in menu discovery
        $missingFromMenu = array_diff($modulesToCheck, $menuKeys);
        if (!empty($missingFromMenu)) {
            $this->warn("Modules in package but NOT in menu customization:");
            foreach ($missingFromMenu as $module) {
                $this->line("  - {$module}");
            }
            $this->newLine();
        } else {
            $this->info("✓ All package modules are in menu customization");
            $this->newLine();
        }

        // Menu items not in package
        $extraInMenu = array_diff($menuKeys, $modulesToCheck);
        $nonModuleItems = ['settings', 'help', 'dashboard', 'notes', 'gdpr', 'mycalendar', 'work', 'hr', 'finance', 'lead', 'clients', 'products', 'orders', 'tickets', 'events', 'messages', 'noticeboard', 'knowledgebase', 'reports'];
        $extraInMenu = array_diff($extraInMenu, $nonModuleItems);
        if (!empty($extraInMenu)) {
            $this->warn("Menu items in customization but NOT in package:");
            foreach ($extraInMenu as $item) {
                $this->line("  - {$item}");
            }
            $this->newLine();
        }

        // Check which module sidebar files exist
        $this->info("=== MODULE SIDEBAR FILES ===");
        $modulePatterns = [
            base_path('Modules/*/Resources/views/sections/sidebar.blade.php'),
            base_path('Modules/Craveva/Modules/*/Resources/views/sections/sidebar.blade.php'),
        ];

        $foundSidebars = [];
        foreach ($modulePatterns as $pattern) {
            $files = glob($pattern);
            if ($files) {
                foreach ($files as $file) {
                    if (preg_match('/Modules[\/\\\\](?:Craveva[\/\\\\]Modules[\/\\\\])?([^\/\\\\]+)[\/\\\\]Resources/', $file, $match)) {
                        $moduleName = strtolower($match[1]);
                        $foundSidebars[$moduleName] = $file;
                    }
                }
            }
        }

        $this->info("Found sidebar files: " . count($foundSidebars));
        foreach ($foundSidebars as $module => $file) {
            $this->line("  {$module}: " . basename(dirname(dirname(dirname($file)))));
        }
        $this->newLine();

        // Check which modules in package have sidebar files
        $this->info("=== MODULES IN PACKAGE WITH SIDEBAR FILES ===");
        foreach ($actualUserModules as $module) {
            if (isset($foundSidebars[$module])) {
                $this->info("  ✓ {$module}");
            } else {
                $this->warn("  ✗ {$module} - no sidebar file found");
            }
        }


        return Command::SUCCESS;
    }
}

