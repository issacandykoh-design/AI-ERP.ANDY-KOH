<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\EmployeeDetails;
use App\Models\RoleUser;
use App\Models\UserPermission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin {--company_id=1 : Company ID} {--name=Admin : Admin name} {--email= : Admin email} {--password= : Admin password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user with full permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            // 获取参数
            $companyId = $this->option('company_id');
            $name = $this->option('name');
            $email = $this->option('email');
            $password = $this->option('password');

            // 如果没有提供邮箱，生成一个
            if (!$email) {
                $timestamp = now()->format('YmdHis');
                $email = "admin{$timestamp}@company.com";
            }

            // 如果没有提供密码，生成一个
            if (!$password) {
                $password = 'Admin@' . rand(1000, 9999);
            }

            // 验证公司是否存在
            $company = Company::find($companyId);
            if (!$company) {
                $this->error("Company with ID {$companyId} not found!");
                return 1;
            }

            // 检查邮箱是否已存在
            if (User::where('email', $email)->exists()) {
                $this->error("User with email {$email} already exists!");
                return 1;
            }

            $this->info("Creating admin user for company: {$company->company_name}");

            // 1. 创建用户认证记录
            $userAuth = new UserAuth();
            $userAuth->email = $email;
            $userAuth->password = Hash::make($password);
            $userAuth->email_verified_at = now();
            $userAuth->save();

            $this->info("✓ User authentication created");

            // 2. 创建用户记录
            $user = new User();
            $user->company_id = $companyId;
            $user->name = $name;
            $user->email = $email;
            $user->status = 'active';
            $user->user_auth_id = $userAuth->id;
            $user->locale = $company->locale ?? 'en';
            $user->admin_approval = 1;
            $user->permission_sync = 1;
            $user->gender = 'male';
            $user->save();

            $this->info("✓ User created with ID: {$user->id}");

            // 3. 创建员工详情
            $employeeDetails = new EmployeeDetails();
            $employeeDetails->user_id = $user->id;
            $employeeDetails->company_id = $companyId;
            $employeeDetails->employee_id = 'ADMIN' . $user->id;
            $employeeDetails->joining_date = now();
            $employeeDetails->added_by = $user->id;
            $employeeDetails->save();

            $this->info("✓ Employee details created");

            // 4. 获取或创建管理员角色
            $adminRole = Role::where('name', 'admin')->where('company_id', $companyId)->first();
            
            if (!$adminRole) {
                $adminRole = new Role();
                $adminRole->company_id = $companyId;
                $adminRole->name = 'admin';
                $adminRole->display_name = 'Admin';
                $adminRole->description = 'Administrator role with full access';
                $adminRole->save();
                $this->info("✓ Admin role created");
            } else {
                $this->info("✓ Using existing admin role");
            }

            // 5. 分配管理员角色
            RoleUser::create([
                'user_id' => $user->id,
                'role_id' => $adminRole->id
            ]);

            $this->info("✓ Admin role assigned to user");

            // 6. 分配所有权限
            $allPermissions = Permission::orderBy('id')->get();
            $permissionTypeAll = PermissionType::where('name', 'all')->first();

            if ($permissionTypeAll && $allPermissions->count() > 0) {
                foreach ($allPermissions as $permission) {
                    UserPermission::firstOrCreate([
                        'user_id' => $user->id,
                        'permission_id' => $permission->id,
                        'permission_type_id' => $permissionTypeAll->id
                    ]);
                }
                $this->info("✓ All permissions assigned ({$allPermissions->count()} permissions)");
            }

            DB::commit();

            // 显示创建的账号信息
            $this->info("\n" . str_repeat('=', 50));
            $this->info("管理员账号创建成功！");
            $this->info(str_repeat('=', 50));
            $this->info("公司: {$company->company_name}");
            $this->info("姓名: {$name}");
            $this->info("邮箱: {$email}");
            $this->info("密码: {$password}");
            $this->info("用户ID: {$user->id}");
            $this->info("状态: 已激活");
            $this->info(str_repeat('=', 50));

            return 0;

        } catch (\Exception $e) {
            DB::rollback();
            $this->error("Error creating admin user: " . $e->getMessage());
            return 1;
        }
    }
}