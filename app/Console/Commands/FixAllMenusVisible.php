<?php

namespace App\Console\Commands;

use App\Models\CustomMenuSetting;
use Illuminate\Console\Command;

class FixAllMenusVisible extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:fix-all-visible {company_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set all menu items to visible for a company (or all companies if no ID provided)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $companyId = $this->argument('company_id');

        if ($companyId) {
            $updated = CustomMenuSetting::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->update(['is_visible' => true]);

            $this->info("Successfully set {$updated} menu items to visible for company ID: {$companyId}");
        } else {
            $updated = CustomMenuSetting::withoutGlobalScopes()
                ->update(['is_visible' => true]);

            $this->info("Successfully set {$updated} menu items to visible for all companies");
        }

        // Clear all caches
        \Cache::flush();
        $this->call('view:clear');
        $this->call('config:clear');
        $this->call('cache:clear');

        $this->info('All caches cleared. Menus should now be visible.');

        return Command::SUCCESS;
    }
}
