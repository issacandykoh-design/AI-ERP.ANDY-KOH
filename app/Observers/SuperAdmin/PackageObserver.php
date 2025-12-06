<?php

namespace App\Observers\SuperAdmin;

use App\Models\PackageUpdateNotify;
use App\Models\SuperAdmin\Package;
use App\Observers\CompanyObserver;

class PackageObserver
{

    public function saving(Package $package)
    {
        if (($package->is_free || $package->default === 'yes') && $package->package != 'lifetime') {
            $package->monthly_status = 1;
            $package->annual_status = 1;
        }
    }

    public function updated(Package $package)
    {
        // Load companies if not already loaded
        if (!$package->relationLoaded('companies')) {
            $package->load('companies');
        }

        // Check if module_in_package was changed by comparing original with current
        $originalModules = $package->getOriginal('module_in_package');
        $currentModules = $package->module_in_package;
        $moduleInPackageChanged = $originalModules !== $currentModules;

        $package->companies->each(function ($company) use ($package, $moduleInPackageChanged) {
            if ($moduleInPackageChanged) {
                (new CompanyObserver())->updateModuleSettings($company);
            }

            $companyEmployeesCount = $company->employees()->count();

            if ($companyEmployeesCount <= $package->max_employees) {
                PackageUpdateNotify::where('company_id', $company->id)->delete();
            }

            clearCompanyValidPackageCache($company->id);
        });

    }

}
