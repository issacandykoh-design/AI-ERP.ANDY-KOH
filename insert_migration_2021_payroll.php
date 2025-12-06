<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('migrations')->insert([
    'migration' => '2021_09_12_061155_payroll_custom_field_table',
    'batch' => DB::table('migrations')->max('batch') + 1,
]);
echo "Marked 2021_09_12_061155_payroll_custom_field_table as ran.\n";

