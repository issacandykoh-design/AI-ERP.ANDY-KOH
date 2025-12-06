<?php

namespace App\Helper;

use Illuminate\Support\Str;

class MenuDiscovery
{
    /**
     * Discover all menu items from the sidebar template
     * Fully dynamic - based on packages/modules assigned to company
     * 
     * BUSINESS LOGIC:
     * 1. Company has a package (package_id)
     * 2. Package has module_in_package (JSON array of module names)
     * 3. CompanyObserver creates/updates ModuleSetting records based on package:
     *    - If module is in package: status='active', is_allowed=1
     *    - If module is NOT in package: status='deactive', is_allowed=0
     * 4. user_modules() returns modules where:
     *    - ModuleSetting: is_allowed=1 AND status='active' (for user's role)
     *    - AND ModuleSwitch: is_enabled=1 (global switch)
     * 5. Menu items should ONLY appear if their module is in user_modules()
     *    - Exception: Non-module items (settings, help, dashboard, notes, gdpr) have their own logic
     */
    public static function discoverMenuItems(?array $enabledModules = null): array
    {
        $menuItems = [];

        // Get enabled modules for current user's company package
        // This is the SOURCE OF TRUTH - it checks:
        // 1. ModuleSetting (is_allowed=1, status='active') - based on package's module_in_package
        // 2. ModuleSwitch (is_enabled=1) - global module enablement
        // 3. User role (admin/employee/client)
        if ($enabledModules === null) {
            $enabledModules = function_exists('user_modules') ? user_modules() : [];
        }
        $enabledModules = array_map('strtolower', $enabledModules); // Normalize to lowercase

        // Read the sidebar menu template
        $menuTemplatePath = resource_path('views/sections/menu.blade.php');
        if (! file_exists($menuTemplatePath)) {
            return [];
        }
        $menuContent = file_get_contents($menuTemplatePath);

        // Extract all x-menu-item components with their attributes
        preg_match_all(
            '/<x-menu-item\s+((?:[^>]|\n)*?)>/is',
            $menuContent,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            // Normalize whitespace in attributes
            $attributes = preg_replace('/\s+/', ' ', trim($match[1]));

            // Extract menu-key
            $menuKey = null;
            if (preg_match('/menu-key=["\']([^"\']+)["\']/', $attributes, $keyMatch)) {
                $menuKey = $keyMatch[1];
            } elseif (preg_match('/:menu-key=["\']([^"\']+)["\']/', $attributes, $keyMatch)) {
                $menuKey = $keyMatch[1];
            }

            if (! $menuKey) {
                continue; // Skip items without menu-key
            }

            // Extract icon
            $icon = 'gear'; // default
            if (preg_match('/icon=["\']([^"\']+)["\']/', $attributes, $iconMatch)) {
                $icon = $iconMatch[1];
            } elseif (preg_match('/:icon=["\']([^"\']+)["\']/', $attributes, $iconMatch)) {
                $icon = $iconMatch[1];
            }

            // Extract text (translation key)
            $textKey = null;
            if (preg_match('/:text="__\(\'([^\']+)\'\)"/', $attributes, $textMatch)) {
                $textKey = $textMatch[1];
            } elseif (preg_match('/:text=\'__\("([^"]+)"\)\'/', $attributes, $textMatch)) {
                $textKey = $textMatch[1];
            } elseif (preg_match('/:text=["\']__\(["\']([^"\']+)["\']\)["\']/', $attributes, $textMatch)) {
                $textKey = $textMatch[1];
            } elseif (preg_match('/:text=["\']([^"\']+)["\']/', $attributes, $textMatch)) {
                $textKey = trim($textMatch[1], "'\"");
            } elseif (preg_match('/text=["\']([^"\']+)["\']/', $attributes, $textMatch)) {
                $textKey = trim($textMatch[1], "'\"");
            }

            // Skip if we already have this menu key (avoid duplicates)
            if (isset($menuItems[$menuKey])) {
                continue;
            }

            $menuItems[$menuKey] = [
                'key' => $menuKey,
                'icon' => $icon,
                'text_key' => $textKey,
            ];
        }

        // Dynamically scan ALL module sidebar files
        $modulePatterns = [
            base_path('Modules/*/Resources/views/sections/sidebar.blade.php'),
            base_path('Modules/Craveva/Modules/*/Resources/views/sections/sidebar.blade.php'),
            resource_path('views/vendor/*/sections/sidebar.blade.php'),
        ];

        $processedFiles = [];

        foreach ($modulePatterns as $pattern) {
            $files = glob($pattern);

            if ($files === false || empty($files)) {
                $baseDir = dirname($pattern);
                if (is_dir($baseDir)) {
                    $subdirs = glob($baseDir.'/*', GLOB_ONLYDIR);
                    if ($subdirs) {
                        foreach ($subdirs as $subdir) {
                            $potentialFile = $subdir.'/Resources/views/sections/sidebar.blade.php';
                            if (file_exists($potentialFile)) {
                                $files[] = $potentialFile;
                            }
                        }
                    }
                }
            }

            if ($files && is_array($files)) {
                foreach ($files as $file) {
                    $realPath = realpath($file);
                    if ($realPath && file_exists($realPath) && ! in_array($realPath, $processedFiles)) {
                        $processedFiles[] = $realPath;
                        self::extractMenuItemsFromModuleFile($realPath, $menuItems);
                    }
                }
            }
        }

        $compositeMenuSubModules = [
            'hr' => ['employees', 'leaves', 'attendance', 'holidays'],
            'work' => ['contracts', 'projects', 'tasks', 'timelogs'],
            'finance' => ['estimates', 'invoices', 'payments', 'expenses', 'bankaccount'],
            'mycalendar' => ['tasks', 'holidays', 'leaves'],
        ];

        // Also add other standalone modules that might be missing
        // Map module names to their menu keys (some modules have different menu keys)
        $moduleToMenuKeyMap = [
            'products' => 'products',
            'sms' => 'sms',
        ];

        foreach ($moduleToMenuKeyMap as $module => $menuKey) {
            if (in_array($module, $enabledModules) && ! isset($menuItems[$menuKey])) {
                // Try to find translation key from sidebar template
                $textKey = 'app.menu.' . $menuKey;
                if ($module === 'notices') {
                    $textKey = 'app.menu.noticeBoard';
                } elseif ($module === 'leads') {
                    $textKey = 'app.menu.lead';
                }
                
                $menuItems[$menuKey] = [
                    'key' => $menuKey,
                    'icon' => 'gear', // default
                    'text_key' => $textKey,
                ];
            }
        }

        
        $result = [];
        $excludeSubModules = [];
        foreach ($compositeMenuSubModules as $subs) {
            foreach ($subs as $sm) {
                $excludeSubModules[] = strtolower($sm);
            }
        }
        $excludeNonStandalone = ['notes'];

        foreach ($menuItems as $menuKey => $item) {
            $moduleName = strtolower($menuKey);

            if (in_array($moduleName, $excludeSubModules)) {
                continue;
            }
            if (in_array($moduleName, $excludeNonStandalone)) {
                continue;
            }

            // Check if this menu item should be included
            $shouldInclude = self::shouldIncludeMenuItem($menuKey, $moduleName, $enabledModules);

            if (! $shouldInclude) {
                continue;
            }

            $defaultName = null;

            if (! empty($item['text_key'])) {
                if (Str::contains($item['text_key'], '::')) {
                    $defaultName = trans($item['text_key']);
                } else {
                    $defaultName = __($item['text_key']);
                }
            }

            // Fallback to formatted menu key if translation fails
            if (empty($defaultName) || $defaultName === $item['text_key']) {
                $defaultName = ucfirst(str_replace(['-', '_'], ' ', $menuKey));
            }

            $result[] = [
                'key' => $menuKey,
                'default_name' => $defaultName,
                'icon' => $item['icon'],
            ];
        }

        return $result;
    }

    /**
     * Determine if a menu item should be included based on package/module configuration
     * FOLLOWS BUSINESS LOGIC: Only modules in package (via user_modules()) should appear
     */
    private static function shouldIncludeMenuItem(string $menuKey, string $moduleName, array $enabledModules): bool
    {
        // Non-module menu items that are always available (not controlled by packages)
        // These have their own visibility logic in the sidebar
        $nonModuleItems = ['settings', 'help', 'dashboard', 'notes', 'gdpr'];
        if (in_array($moduleName, $nonModuleItems)) {
            // These items have their own conditions in sidebar (permissions, settings, etc.)
            // For menu customization, we show them if they exist in the template
            // The sidebar will handle the actual visibility logic
            return true;
        }

        // Composite menu items (work, hr, finance, mycalendar)
        // Show if ANY of their sub-modules are enabled in the package
        $compositeMenus = [
            'mycalendar' => ['tasks', 'events', 'holidays', 'tickets', 'leaves'],
            'work' => ['contracts', 'projects', 'tasks', 'timelogs'],
            'hr' => ['employees', 'leaves', 'attendance', 'holidays'],
            'finance' => ['estimates', 'invoices', 'payments', 'expenses', 'bankaccount'],
        ];

        if (isset($compositeMenus[$moduleName])) {
            // Check if any sub-module is enabled in the package
            foreach ($compositeMenus[$moduleName] as $subModule) {
                if (in_array(strtolower($subModule), $enabledModules)) {
                    return true;
                }
            }
            return false;
        }

        // Business rule: Products only shows if Purchase is NOT enabled
        if ($moduleName === 'products') {
            return in_array('products', $enabledModules) && ! in_array('purchase', $enabledModules);
        }

        // Special handling for menu keys that don't match module names exactly
        $menuKeyToModuleMap = [
            'servermanager' => 'servermanager',
            'serverManager' => 'servermanager',
            'noticeboard' => 'notices',
            'noticeBoard' => 'notices',
            'lead' => 'leads',
            'mycalendar' => 'mycalendar',
            'myCalendar' => 'mycalendar',
        ];

        // Check if menu key maps to a module name
        $actualModuleName = $menuKeyToModuleMap[$menuKey] ?? $moduleName;

        // For all other menu items, check if the module is enabled in the package
        // This is the core business logic: menu items = modules in package
        // Check both the menu key and the mapped module name
        return in_array($moduleName, $enabledModules) || in_array($actualModuleName, $enabledModules);
    }

    /**
     * Extract menu items from a module sidebar file
     */
    private static function extractMenuItemsFromModuleFile(string $filePath, array &$menuItems): void
    {
        if (! file_exists($filePath)) {
            return;
        }

        $moduleContent = file_get_contents($filePath);

        preg_match_all(
            '/<x-menu-item\s+([^>]*?)(?:\s*\/)?>/is',
            $moduleContent,
            $moduleMatches,
            PREG_SET_ORDER
        );

        foreach ($moduleMatches as $match) {
            $fullTag = $match[0];
            $attributes = isset($match[1]) ? trim($match[1]) : '';
            $attributes = preg_replace('/[\r\n\s]+/', ' ', $attributes);

            $menuKey = null;
            if (preg_match('/menu-key=["\']([^"\']+)["\']/', $attributes, $keyMatch)) {
                $menuKey = $keyMatch[1];
            }

            if (! $menuKey && preg_match('/menu-key=["\']([^"\']+)["\']/', $fullTag, $keyMatch)) {
                $menuKey = $keyMatch[1];
            }

            if (! $menuKey || isset($menuItems[$menuKey])) {
                continue;
            }

            $icon = 'gear';
            if (preg_match('/icon=["\']([^"\']+)["\']/', $attributes, $iconMatch)) {
                $icon = $iconMatch[1];
            } elseif (preg_match('/icon=["\']([^"\']+)["\']/', $fullTag, $iconMatch)) {
                $icon = $iconMatch[1];
            }

            $textKey = null;
            if (preg_match('/:text=["\']__\(["\']([^"\']+)["\']\)["\']/', $attributes, $textMatch)) {
                $textKey = $textMatch[1];
            } elseif (preg_match('/:text=["\']__\(["\']([^"\']+)["\']\)["\']/', $fullTag, $textMatch)) {
                $textKey = $textMatch[1];
            } elseif (preg_match('/:text="__\(\'([^\']+)\'\)"/', $attributes, $textMatch)) {
                $textKey = $textMatch[1];
            } elseif (preg_match('/:text="__\(\'([^\']+)\'\)"/', $fullTag, $textMatch)) {
                $textKey = $textMatch[1];
            } elseif (preg_match('/:text=\'__\("([^"]+)"\)\'/', $attributes, $textMatch)) {
                $textKey = $textMatch[1];
            } elseif (preg_match('/:text=["\']([^"\']+)["\']/', $attributes, $textMatch)) {
                $textKey = trim($textMatch[1], "'\"");
            }

            $menuItems[$menuKey] = [
                'key' => $menuKey,
                'icon' => $icon,
                'text_key' => $textKey,
            ];
        }
    }
}
