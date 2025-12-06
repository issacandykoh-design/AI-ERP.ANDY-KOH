<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use App\Models\SuperAdmin\GlobalCurrency;
use App\Models\SuperAdmin\Package;
use Illuminate\Database\Seeder;
use App\Models\Company;
use Illuminate\Support\Facades\App;

class OrganisationSettingsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $defaultDriver = config('session.driver') == 'database' ? 'database' : 'file';

        $appName = 'craveva';


        $globalSetting = new GlobalSetting();
        $globalSetting->global_app_name = $appName;
        $globalSetting->locale = 'en';
        $globalSetting->google_recaptcha_status = 'deactive';
        $globalSetting->google_recaptcha_v2_status = 'deactive';
        $globalSetting->google_recaptcha_v3_status = 'deactive';
        $globalSetting->app_debug = false;

        // SAAS
        $globalCurrency = GlobalCurrency::first();
        $globalSetting->currency_id = $globalCurrency->id;

        $globalSetting->rtl = false;
        $globalSetting->hide_cron_message = 0;
        $globalSetting->system_update = 1;
        $globalSetting->show_review_modal = 1;
        $globalSetting->auth_theme = 'light';
        $globalSetting->session_driver = $defaultDriver;
        $globalSetting->allowed_file_size = 10;
        $globalSetting->moment_format = 'DD-MM-YYYY';
        $globalSetting->sidebar_logo_style = 'square';
        $globalSetting->allowed_file_types = 'image/*,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/docx,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-zip-compressed, application/x-compressed, multipart/x-zip,.xlsx,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,application/sla,.stl';
        $globalSetting->show_update_popup = 1;
        $globalSetting->hash = md5(microtime());
        $globalSetting->save();

        $setting = new Company();
        $setting->company_name = $appName;
        $setting->app_name = $appName;
        $setting->company_email = 'company@email.com';
        $setting->company_phone = '1234567891';
        $setting->address = 'Your Company address here';
        $setting->website = 'https://craveva.biz';
        $setting->date_format = 'd-m-Y';

        $setting->save();

        Package::where('name', 'Trial')->whereNull('currency_id')->update(['currency_id' => $globalSetting->currency_id]);

        if (!App::environment('craveva')) {
            // Check if Faker is available
            if (!function_exists('fake') && !class_exists('\Faker\Factory')) {
                $this->command->warn('Faker not available, skipping additional company seeding');
                return;
            }
            
            $seedCount = config('app.extra_company_seed_count');

            for ($i = 0; $i < $seedCount; $i++) {
                $this->command->info('Seeding company: ' . ($i + 1) . ' Remaining:' . ($seedCount - $i));

                $companyName = function_exists('fake') ? fake()->company() : 'Company ' . ($i + 1);

                Company::create([
                    'company_name' => $companyName,
                    'app_name' => $companyName,
                    'company_email' => function_exists('fake') ? fake()->unique()->safeEmail() : 'company' . ($i + 1) . '@example.com',
                    'company_phone' => function_exists('fake') ? fake()->phoneNumber() : '123456789' . $i,
                    'address' => function_exists('fake') ? fake()->address() : '123 Main St',
                    'created_at' => function_exists('fake') ? fake()->dateTimeBetween(now()->subMonths(5), now()) : now(),
                    'website' => 'https://craveva.biz',
                ]);
            }
        }
    }

}
