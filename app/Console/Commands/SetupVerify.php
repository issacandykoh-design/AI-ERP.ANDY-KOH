<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetupVerify extends Command
{
    protected $signature = 'app:setup-verify {--clear} {--fix-cache}';
    protected $description = 'Verify application setup and environment';

    public function handle()
    {
        $tests = [];

        $tests[] = ['name' => 'Environment', 'status' => app()->environment(), 'ok' => app()->environment() !== 'local'];

        $dbOk = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbOk = false;
            $this->error($e->getMessage());
        }
        $tests[] = ['name' => 'Database', 'status' => $dbOk ? 'connected' : 'fail', 'ok' => $dbOk];

        $storageOk = is_writable(storage_path()) && is_writable(storage_path('logs')) && is_writable(base_path('bootstrap/cache'));
        $tests[] = ['name' => 'StorageWritable', 'status' => $storageOk ? 'ok' : 'fail', 'ok' => $storageOk];

        $fortifyOk = class_exists(\App\Actions\Fortify\RedirectIfTwoFactorConfirmed::class);
        $tests[] = ['name' => 'FortifyRedirectClass', 'status' => $fortifyOk ? 'exists' : 'missing', 'ok' => $fortifyOk];

        $inputGroupPath = resource_path('views/components/forms/input-group.blade.php');
        $inputPropsOk = is_file($inputGroupPath) && Str::contains((string) file_get_contents($inputGroupPath), '@props');
        $tests[] = ['name' => 'InputGroupProps', 'status' => $inputPropsOk ? 'ok' : 'missing', 'ok' => $inputPropsOk];

        $composerWarn = false;
        $composerPath = base_path('composer.json');
        if (is_file($composerPath)) {
            $json = json_decode((string) file_get_contents($composerPath), true);
            $scripts = data_get($json, 'scripts.post-update-cmd', []);
            foreach ($scripts as $s) {
                if (is_string($s) && Str::contains($s, 'migrate')) {
                    $composerWarn = true;
                    break;
                }
            }
        }
        if ($composerWarn) {
            $this->warn('Composer post-update triggers migrations; disable for safer deploys');
        }

        foreach ($tests as $t) {
            $this->line(($t['ok'] ? '[OK] ' : '[FAIL] ').$t['name'].' = '.$t['status']);
        }

        if ($this->option('clear')) {
            Artisan::call('optimize:clear');
            $this->line('[DONE] optimize:clear');
        }

        if ($this->option('fix-cache')) {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $this->line('[DONE] cache rebuilt');
        }

        return 0;
    }
}

