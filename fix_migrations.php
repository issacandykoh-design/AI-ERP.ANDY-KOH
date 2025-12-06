<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Migration Fixer...\n";

// Get all migration files
$files = File::files(database_path('migrations'));
$migrations = [];

foreach ($files as $file) {
    $migrations[$file->getFilenameWithoutExtension()] = $file->getPathname();
}

// Get already run migrations
$ran = DB::table('migrations')->pluck('migration')->toArray();

$batch = DB::table('migrations')->max('batch') + 1;
$fixed = 0;

foreach ($migrations as $name => $path) {
    if (in_array($name, $ran)) {
        continue;
    }

    $content = file_get_contents($path);
    
    // improved regex to catch Schema::create('tablename'
    if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
        $table = $matches[1];
        
        if (Schema::hasTable($table)) {
            echo "Marking '$name' as done (Table '$table' exists).\n";
            DB::table('migrations')->insert([
                'migration' => $name,
                'batch' => $batch
            ]);
            $fixed++;
        } else {
            // Table doesn't exist, so we should let artisan migrate run this one
            // But wait, what if it's a migration that alters a table?
            // If it's an ALTER (Schema::table), we can't easily check if it's done.
            // For now, we focus on CREATE TABLE conflicts.
        }
    } else {
        // Could be an ALTER or other operation. 
        // If it fails later, we'll deal with it.
    }
}

echo "Fixed $fixed migrations.\n";
