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
            'email_verified_at' => now(),
        ]
    );
    echo "UserAuth created/updated.\n";

    // 2. Create/Update User
    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => 'Super Admin',
            'user_auth_id' => $userAuth->id,
            'is_superadmin' => 1,
        ]
    );
    echo "User created/updated.\n";

    // 3. Assign Role
    $role = Role::where('name', 'superadmin')->first();
    if ($role) {
        if (!$user->roles->contains($role->id)) {
            $user->roles()->attach($role->id);
            echo "Role 'superadmin' attached.\n";
        } else {
            echo "Role 'superadmin' already attached.\n";
        }
    } else {
        echo "Error: Role 'superadmin' not found!\n";
    }
    
    echo "SUCCESS: Account created.\nEmail: $email\nPassword: $password\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
