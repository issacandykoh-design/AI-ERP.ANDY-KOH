<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- DEALS TABLE ---\n";
try {
    $deals = DB::select('SHOW CREATE TABLE deals');
    foreach ($deals as $row) {
        echo $row->{'Create Table'} . "\n";
    }
} catch (\Exception $e) {
    echo "Error showing deals: " . $e->getMessage() . "\n";
}

echo "\n--- LEAD_FOLLOW_UP TABLE ---\n";
try {
    $lfu = DB::select('SHOW CREATE TABLE lead_follow_up');
    foreach ($lfu as $row) {
        echo $row->{'Create Table'} . "\n";
    }
} catch (\Exception $e) {
    echo "Error showing lead_follow_up: " . $e->getMessage() . "\n";
}
