<?php

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $packages = Package::all();

        foreach ($packages as $package) {
            $modules = json_decode($package->module_in_package, true) ?: [];

            if (!in_array('menu_customization', $modules)) {
                // Enable for all non-free packages
                if (!$package->is_free) {
                    $modules[] = 'menu_customization';
                    $package->module_in_package = json_encode($modules);
                    $package->saveQuietly();
                }
            }
        }

        // Ensure module registry exists for permission wiring
        Module::firstOrCreate(['module_name' => 'menu_customization']);

        $companies = Company::with('package')->get();

        foreach ($companies as $company) {
            $mods = $company->package ? json_decode($company->package->module_in_package, true) : [];
            if (in_array('menu_customization', $mods)) {
                ModuleSetting::createRoleSettingEntry('menu_customization', ['admin'], $company);
            }
        }
    }

    public function down(): void
    {
        // No rollback for package module changes
    }
};
