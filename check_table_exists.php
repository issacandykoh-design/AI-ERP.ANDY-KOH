<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$table = $argv[1] ?? null;
if (!$table) {
    echo "Usage: php check_table_exists.php <table>\n";
    exit(1);
}

$db = env('DB_DATABASE');
$exists = DB::selectOne('SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ? AND table_name = ?', [$db, $table]);
echo ($exists && $exists->c > 0) ? "1\n" : "0\n";

