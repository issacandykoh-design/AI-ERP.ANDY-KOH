<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('migrations')->insert([
    'migration' => '2019_10_18_111743_create_employee_monthly_salary_table',
    'batch' => DB::table('migrations')->max('batch') + 1,
]);
echo "Marked 2019_10_18_111743_create_employee_monthly_salary_table as ran.\n";

