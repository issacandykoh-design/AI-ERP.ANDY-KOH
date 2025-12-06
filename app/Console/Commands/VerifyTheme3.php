<?php

namespace App\Console\Commands;

use App\Models\GlobalSetting;
use App\Models\SuperAdmin\FrontDetail;
use Illuminate\Console\Command;

class VerifyTheme3 extends Command
{
    protected $signature = 'theme3:verify';
    protected $description = 'Verify Theme 3 is properly configured and working';

    public function handle()
    {
        $this->info('🔍 Verifying Theme 3 Configuration...');
        $this->newLine();

        $allGood = true;

        // Check global settings
        $global = GlobalSetting::first();
        if (!$global) {
            $this->error('❌ Global settings not found!');
            return 1;
        }

        // Check Theme 3 is set
        if ($global->front_design == 2) {
            $this->info('✅ Theme 3 is active (front_design = 2)');
        } else {
            $this->error('❌ Theme 3 is NOT active. Current: front_design = ' . $global->front_design);
            $allGood = false;
        }

        // Check homepage setting
        if ($global->setup_homepage == 'default') {
            $this->info('✅ Homepage is set to "default"');
        } else {
            $this->warn('⚠️  Homepage is set to: ' . $global->setup_homepage);
        }

        // Check frontend is enabled
        if (!$global->frontend_disable) {
            $this->info('✅ Frontend is enabled');
        } else {
            $this->error('❌ Frontend is disabled!');
            $allGood = false;
        }

        // Check front detail
        $frontDetail = FrontDetail::first();
        if ($frontDetail) {
            $this->info('✅ Front Detail exists');
            $this->line('   - Primary Color: ' . ($frontDetail->primary_color ?? 'not set'));
            $this->line('   - Locale: ' . ($frontDetail->locale ?? 'not set'));
        } else {
            $this->error('❌ Front Detail not found!');
            $allGood = false;
        }

        // Check landing page
        $landingPath = public_path('landing/index.html');
        if (file_exists($landingPath)) {
            $this->info('✅ Theme 3 landing page exists');
            $fileSize = filesize($landingPath);
            $this->line('   - File size: ' . number_format($fileSize / 1024, 2) . ' KB');
        } else {
            $this->error('❌ Theme 3 landing page NOT found at: ' . $landingPath);
            $allGood = false;
        }

        $this->newLine();
        if ($allGood) {
            $this->info('🎉 All checks passed! Theme 3 is properly configured.');
            $this->newLine();
            $this->line('📝 To test Theme 3:');
            $this->line('   1. Visit: ' . config('app.url'));
            $this->line('   2. You should see the modern React landing page');
            $this->line('   3. Check browser console for any errors');
        } else {
            $this->error('❌ Some checks failed. Please fix the issues above.');
            $this->newLine();
            $this->line('💡 Run: php artisan theme3:setup');
        }

        return $allGood ? 0 : 1;
    }
}

