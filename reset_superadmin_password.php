<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'superadmin@example.com';
$newPassword = '123456';

$updated = \App\Models\UserAuth::where('email', $email)->update([
    'password' => bcrypt($newPassword),
]);

if ($updated) {
    echo "Password reset for {$email}\n";
} else {
    echo "User not found: {$email}\n";
}

