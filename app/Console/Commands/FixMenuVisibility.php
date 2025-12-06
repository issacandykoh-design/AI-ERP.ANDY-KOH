<?php

namespace App\Console\Commands;

use App\Models\CustomMenuSetting;
use Illuminate\Console\Command;

class FixMenuVisibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:fix-visibility {company_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all menu items to visible for a company';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $companyId = $this->argument('company_id');

        if (!$companyId) {
            $this->error('Please provide a company ID');
            $this->info('Usage: php artisan menu:fix-visibility {company_id}');
            $this->info('Example: php artisan menu:fix-visibility 1');
            return 1;
        }

        $this->info("Fixing menu visibility for company ID: {$companyId}");

        $updated = CustomMenuSetting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->update(['is_visible' => true]);

        $this->info("Updated {$updated} menu items to visible.");

        // Clear cache
        \Cache::flush();
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');

        $this->info('Cache cleared. Please refresh your browser.');

        return 0;
    }
}

