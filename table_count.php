<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$db = env('DB_DATABASE');
$rows = DB::select('SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ?', [$db]);
echo ($rows && isset($rows[0])) ? $rows[0]->c . "\n" : "0\n";

