#!/bin/bash
set -e

echo "--- STARTING FIXES ---"

# 1. Fix form.blade.php
echo "Fixing form.blade.php..."
cat > /var/www/html/resources/views/components/form.blade.php << 'EOF'
@props(['method' => 'POST', 'spoofMethod' => false])
<form method="{{ ($spoofMethod ?? false) ? 'POST' : $method }}" {!! $attributes !!} autocomplete="off">
    @include('sections.password-autocomplete-hide')

    <input type="hidden" id="redirect_url" name="redirect_url" value="{{ request()->redirectUrl }}">

    @unless(in_array($method, ['HEAD', 'GET', 'OPTIONS']))
        @csrf
    @endunless

    @if ($spoofMethod ?? false)
        @method($method)
    @endif

    {!! $slot !!}
</form>
EOF

# 2. Create Admin Script
echo "Creating Admin Script..."
cat > /var/www/html/create_admin.php << 'EOF'
<?php
use App\Models\User;
use App\Models\UserAuth;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Admin Creation...\n";

$email = 'superadmin@craveva.com';
$password = 'Craveva2024!';

try {
    // 1. Create/Update UserAuth
    $userAuth = UserAuth::updateOrCreate(
        ['email' => $email],
        [
            'password' => Hash::make($password),
            'email_verified_at' => now()
        ]
    );

    // 2. Create/Update User
    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => 'Super Admin',
            'user_auth_id' => $userAuth->id,
            'is_superadmin' => 1
        ]
    );

    // 3. Assign Role
    $role = Role::where('name', 'superadmin')->first();
    if ($role) {
        if (!$user->roles->contains($role->id)) {
            $user->roles()->attach($role->id);
            echo "Role attached.\n";
        }
    }

    echo "SUCCESS: Account created.\nEmail: $email\nPassword: $password\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
EOF

# 3. Run Admin Script
echo "Running Admin Script..."
php /var/www/html/create_admin.php

# 4. Fix .env
echo "Updating .env..."
cd /var/www/html

# Fix APP_URL to https
sed -i 's|APP_URL=http://hub.craveva.com|APP_URL=https://hub.craveva.com|g' .env

# Fix Pusher (remove empty keys to avoid JS errors if they are empty)
# We check if they are empty or not present, and force dummy values if so.
# But wait, if they are already there but empty, sed handles it.
if grep -q "PUSHER_APP_KEY=$" .env; then
    sed -i 's|PUSHER_APP_KEY=|PUSHER_APP_KEY=dummy_key|g' .env
fi
if grep -q "PUSHER_APP_ID=$" .env; then
    sed -i 's|PUSHER_APP_ID=|PUSHER_APP_ID=dummy_id|g' .env
fi
if grep -q "PUSHER_APP_SECRET=$" .env; then
    sed -i 's|PUSHER_APP_SECRET=|PUSHER_APP_SECRET=dummy_secret|g' .env
fi

# Fix Session
if ! grep -q "SESSION_DOMAIN" .env; then
    echo "" >> .env
    echo "SESSION_DOMAIN=hub.craveva.com" >> .env
fi
# Also ensure secure cookie is true
if ! grep -q "SESSION_SECURE_COOKIE" .env; then
    echo "SESSION_SECURE_COOKIE=true" >> .env
fi

# 5. Fix Permissions & Clear Cache
echo "Fixing Permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "Clearing Caches..."
php artisan config:clear
php artisan view:clear

echo "Cleaning up..."
rm /var/www/html/create_admin.php

echo "--- DONE ---"
