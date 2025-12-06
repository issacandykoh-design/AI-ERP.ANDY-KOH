<?php

use Illuminate\Support\Facades\File;
use Modules\Craveva\Entities\CravevaModuleInstall;

if (! function_exists('isInstallFromCravevaModule')) {

    function isInstallFromCravevaModule($name)
    {
        return CravevaModuleInstall::where('module_name', $name)->exists();
    }

}

if (! function_exists('getCravevaModules')) {

    function getCravevaModules()
    {
        return CravevaModuleInstall::all();
    }

}

if (! function_exists('getCravevaModule')) {

    function getCravevaModule($name)
    {
        return CravevaModuleInstall::where('module_name', $name)->first();
    }

}

if (! function_exists('getCravevaModulesPath')) {

    function getCravevaModulesPath()
    {
        return module_path('Craveva', 'Modules');
    }

}

if (! function_exists('getCravevaAvailableModules')) {

    function getCravevaAvailableModules()
    {
        $modulesPath = getCravevaModulesPath();
        $modules = [];

        if (file_exists($modulesPath)) {
            // get only directories
            $modules = File::directories($modulesPath);
            // remove path from array
            $modules = array_map(function ($module) use ($modulesPath) {
                return str_replace($modulesPath.'/', '', $module);
            }, $modules);
        }

        return $modules;
    }

}

if (! function_exists('getCravevaAvailableForInstallModules')) {

    function getCravevaAvailableForInstallModules()
    {
        $modules = getCravevaAvailableModules();
        $installedModules = array_keys(\Nwidart\Modules\Facades\Module::all());

        $availableModules = [];

        foreach ($modules as $module) {
            if (in_array($module, $installedModules)) {
                // check version of installedModule and compare with version of bundle module
                // get version of from version.txt
                $moduleVersion = File::get(getCravevaModulesPath() . '/' .$module .'/version.txt');
                $installedModuleVersion = File::get(base_path('Modules' . '/' . $module . '/version.txt'));

                if ($moduleVersion > $installedModuleVersion) {
                    $availableModules[] = $module;
                }
            }
            else {
                $availableModules[] = $module;
            }
        }

        return $availableModules;
    }

}
