<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\Permission;
use App\Scopes\CompanyScope;
use App\Models\UserPermission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateSuperAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-superadmin {--name= : Superadmin name} {--email= : Superadmin email} {--password= : Superadmin password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new superadmin user with full superadmin permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            // Get parameters
            $name = $this->option('name');
            $email = $this->option('email');
            $password = $this->option('password');

            // If no email provided, generate one
            if (!$email) {
                $timestamp = now()->format('YmdHis');
                $email = "superadmin{$timestamp}@example.com";
            }

            // If no password provided, generate one
            if (!$password) {
                $password = 'SuperAdmin@' . rand(1000, 9999);
            }

            // If no name provided, generate one
            if (!$name) {
                $name = 'Super Administrator';
            }

            // Check if email already exists
            if (User::where('email', $email)->exists()) {
                $this->error("User with email {$email} already exists!");
                return 1;
            }

            $this->info("Creating superadmin user...");

            // 1. Create user authentication record
            $userAuth = UserAuth::create([
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now()
            ]);

            $this->info("✓ User authentication created");

            // 2. Create superadmin user
            $superadmin = new User();
            $superadmin->name = $name;
            $superadmin->email = $email;
            $superadmin->is_superadmin = true;
            $superadmin->status = 'active';
            $superadmin->user_auth_id = $userAuth->id;
            $superadmin->admin_approval = 1;
            $superadmin->permission_sync = 1;
            $superadmin->gender = 'male';
            $superadmin->save();

            $this->info("✓ Superadmin user created with ID: {$superadmin->id}");

            // 3. Attach superadmin role and permissions
            $this->superadminRolePermissionAttach($superadmin);

            $this->info("✓ Superadmin role and permissions assigned");

            DB::commit();

            // Display created account information
            $this->info("\n" . str_repeat('=', 50));
            $this->info("Superadmin Account Created Successfully!");
            $this->info(str_repeat('=', 50));
            $this->info("Name: {$name}");
            $this->info("Email: {$email}");
            $this->info("Password: {$password}");
            $this->info("User ID: {$superadmin->id}");
            $this->info("Status: Active");
            $this->info("Type: Superadmin");
            $this->info(str_repeat('=', 50));

            return 0;

        } catch (\Exception $e) {
            DB::rollback();
            $this->error("Error creating superadmin user: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Attach superadmin role and permissions to user
     *
     * @param User $user
     * @return void
     */
    private function superadminRolePermissionAttach(User $user)
    {
        // Get superadmin role
        $superadminRole = Role::withoutGlobalScopes([CompanyScope::class])
            ->whereNull('company_id')
            ->where('name', 'superadmin')
            ->first();

        if (!$superadminRole) {
            $this->error("Superadmin role not found! Please run the SuperAdminRoleTableSeeder first.");
            throw new \Exception("Superadmin role not found");
        }

        // Attach role to user
        $user->roles()->attach($superadminRole->id);

        // Get all superadmin permissions
        $permissions = Permission::select('permissions.*')
            ->whereHas('module', function ($query) {
                $query->withoutGlobalScopes()->where('is_superadmin', '1');
            })
            ->get();

        // Prepare user permissions array
        $userPermission = [];
        foreach ($permissions as $permission) {
            $userPermission[] = [
                'user_id' => $user->id,
                'permission_id' => $permission->id,
                'permission_type_id' => 4, // All permissions
            ];
        }

        // Insert permissions in chunks
        foreach (array_chunk($userPermission, 200) as $userPermissionChunk) {
            UserPermission::insert($userPermissionChunk);
        }

        $this->info("✓ Assigned {$permissions->count()} permissions to superadmin");
    }
}