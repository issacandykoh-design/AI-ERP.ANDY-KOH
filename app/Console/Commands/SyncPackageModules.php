<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\SuperAdmin\Package;
use App\Observers\CompanyObserver;
use Illuminate\Console\Command;

class SyncPackageModules extends Command
{
    protected $signature = 'package:sync-modules {--company= : Sync for specific company ID only}';

    protected $description = 'Sync ModuleSetting records with package modules for all companies (or specific company)';

    public function handle()
    {
        $companyId = $this->option('company');

        if ($companyId) {
            $companies = Company::where('id', $companyId)->get();
            if ($companies->isEmpty()) {
                $this->error("Company ID {$companyId} not found.");

                return Command::FAILURE;
            }
        } else {
            $companies = Company::all();
        }

        $this->info("Syncing modules for {$companies->count()} company(ies)...\n");

        $observer = new CompanyObserver;
        $totalSynced = 0;
        $totalIssues = 0;

        foreach ($companies as $company) {
            $this->line("Processing Company ID: {$company->id} ({$company->company_name})");

            $package = Package::find($company->package_id);
            if (! $package) {
                $this->warn("  ⚠ Package not found for company {$company->id}");
                $totalIssues++;

                continue;
            }

            // Use the observer's updateModuleSettings method
            try {
                $observer->updateModuleSettings($company);
                $this->info("  ✓ Synced modules for company {$company->id}");

                // Clear user caches
                $observer->clearCompanyUserCache($company);
                $totalSynced++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error syncing company {$company->id}: {$e->getMessage()}");
                $totalIssues++;
            }
        }

        $this->newLine();
        $this->info('=== SUMMARY ===');
        $this->info("Companies synced: {$totalSynced}");
        if ($totalIssues > 0) {
            $this->warn("Issues encountered: {$totalIssues}");
        } else {
            $this->info('✓ All companies synced successfully!');
        }

        return Command::SUCCESS;
    }
}
