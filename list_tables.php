<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$db = env('DB_DATABASE');
$rows = DB::select('SELECT table_name AS tname FROM information_schema.tables WHERE table_schema = ? AND table_name IN (\'packages\', \'package_settings\', \'offline_invoices\') ORDER BY table_name', [$db]);
foreach ($rows as $r) { echo $r->tname . "\n"; }
