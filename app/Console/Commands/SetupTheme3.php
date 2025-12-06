<?php

namespace App\Console\Commands;

use App\Models\GlobalSetting;
use App\Models\SuperAdmin\FrontDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SetupTheme3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme3:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up Theme 3 (Modern Landing Page) as the active theme';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🎨 Setting up Theme 3...');
        $this->newLine();

        try {
            // Get global settings
            $global = GlobalSetting::first();
            
            if (!$global) {
                $this->error('❌ Error: Global settings not found!');
                return 1;
            }
            
            $this->info('📊 Current Settings:');
            $this->line('   - Front Design: ' . $global->front_design . ' (0=Theme1, 1=Theme2, 2=Theme3)');
            $this->line('   - Setup Homepage: ' . ($global->setup_homepage ?? 'not set'));
            $this->line('   - Frontend Disabled: ' . ($global->frontend_disable ? 'Yes' : 'No'));
            $this->newLine();
            
            // Set Theme 3
            $this->info('✅ Setting Theme 3 (front_design = 2)...');
            $global->front_design = 2;
            
            // Set homepage to default
            $this->info('✅ Setting homepage to \'default\'...');
            $global->setup_homepage = 'default';
            
            // Ensure frontend is enabled
            $this->info('✅ Ensuring frontend is enabled...');
            $global->frontend_disable = 0;
            
            $global->save();
            
            // Clear cache
            $this->info('✅ Clearing global_setting cache...');
            Cache::forget('global_setting');
            
            // Get front detail
            $frontDetail = FrontDetail::first();
            if ($frontDetail) {
                $this->info('✅ Front Detail found');
                $this->line('   - Primary Color: ' . ($frontDetail->primary_color ?? 'not set'));
                $this->line('   - Locale: ' . ($frontDetail->locale ?? 'not set'));
            } else {
                $this->warn('⚠️  Warning: Front Detail not found, creating default...');
                $frontDetail = new FrontDetail();
                $frontDetail->primary_color = '#453130';
                $frontDetail->locale = 'en';
                $frontDetail->save();
                $this->info('✅ Default Front Detail created');
            }
            
            // Verify landing page exists
            $landingPath = public_path('landing/index.html');
            if (file_exists($landingPath)) {
                $this->info('✅ Theme 3 landing page found at: ' . $landingPath);
            } else {
                $this->error('❌ Error: Theme 3 landing page NOT found at: ' . $landingPath);
                $this->line('   Please ensure the landing page exists!');
            }
            
            $this->newLine();
            $this->info('🎉 Theme 3 Setup Complete!');
            $this->newLine();
            $this->info('📋 Summary:');
            $this->line('   - Front Design: ' . $global->front_design . ' (Theme 3)');
            $this->line('   - Setup Homepage: ' . $global->setup_homepage);
            $this->line('   - Frontend Disabled: ' . ($global->frontend_disable ? 'Yes' : 'No'));
            $this->line('   - Primary Color: ' . ($frontDetail->primary_color ?? 'not set'));
            $this->newLine();
            
            $this->info('🔍 Next Steps:');
            $this->line('   1. Clear all caches: php artisan optimize:clear');
            $this->line('   2. Visit your homepage to see Theme 3');
            $this->line('   3. Check Super Admin → Front Settings → Theme Settings');
            $this->line('      Theme 3 should be selected in the dropdown');
            $this->newLine();
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->line('   File: ' . $e->getFile());
            $this->line('   Line: ' . $e->getLine());
            return 1;
        }
    }
}

