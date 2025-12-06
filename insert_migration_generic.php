<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$name = $argv[1] ?? null;
if (!$name) {
    echo "Usage: php insert_migration_generic.php <migration_name>\n";
    exit(1);
}

DB::table('migrations')->insert([
    'migration' => $name,
    'batch' => DB::table('migrations')->max('batch') + 1,
]);
echo "Marked $name as ran.\n";

