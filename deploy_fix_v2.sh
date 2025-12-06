#!/bin/bash
set -e

echo "--- STARTING THEME FIX ---"

# Create Theme Fix Script
echo "Creating Theme Fix Script..."
cat > /var/www/html/fix_theme.php << 'EOF'
<?php
use App\Models\ThemeSetting;
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Theme Settings...\n";

$themes = [
    'superadmin' => [
        'header_color' => '#1d82f5',
        'sidebar_color' => '#171F29',
        'sidebar_text_color' => '#99A5B5',
        'link_color' => '#F7FAFF',
        'restrict_admin_theme_change' => 0 // Important
    ],
    'admin' => [
        'header_color' => '#1d82f5',
        'sidebar_color' => '#171F29',
        'sidebar_text_color' => '#99A5B5',
        'link_color' => '#F7FAFF',
    ],
    'employee' => [
        'header_color' => '#1d82f5',
        'sidebar_color' => '#171F29',
        'sidebar_text_color' => '#99A5B5',
        'link_color' => '#F7FAFF',
    ],
    'client' => [
        'header_color' => '#1d82f5',
        'sidebar_color' => '#171F29',
        'sidebar_text_color' => '#99A5B5',
        'link_color' => '#F7FAFF',
    ],
];

foreach ($themes as $panel => $settings) {
    $theme = ThemeSetting::withoutGlobalScopes()->where('panel', $panel)->first();
    if (!$theme) {
        echo "Creating theme for $panel...\n";
        $theme = new ThemeSetting();
        $theme->panel = $panel;
        $theme->header_color = $settings['header_color'];
        $theme->sidebar_color = $settings['sidebar_color'];
        $theme->sidebar_text_color = $settings['sidebar_text_color'];
        $theme->link_color = $settings['link_color'];
        if (isset($settings['restrict_admin_theme_change'])) {
            $theme->restrict_admin_theme_change = $settings['restrict_admin_theme_change'];
        }
        $theme->save();
        echo "Created.\n";
    } else {
        echo "Theme for $panel exists.\n";
    }
}

echo "SUCCESS: Theme settings verified.\n";
EOF

# Run Script
echo "Running Theme Fix Script..."
php /var/www/html/fix_theme.php

# Clear Session and Cache
echo "Clearing Session and Cache..."
php artisan config:clear
php artisan view:clear
php artisan cache:clear
rm -f /var/www/html/storage/framework/sessions/*

echo "Cleaning up..."
rm /var/www/html/fix_theme.php

echo "--- DONE ---"
