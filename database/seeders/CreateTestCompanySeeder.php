<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\EmployeeDetails;
use App\Models\Role;
use App\Models\Permission;

class CreateTestCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();
            
            // 生成随机公司信息
            $timestamp = date('YmdHis');
            $companyName = 'Test Company ' . $timestamp;
            $adminEmail = 'admin' . $timestamp . '@testcompany.com';
            $adminPassword = 'password123';
            
            $this->command->info("正在创建公司: $companyName");
            
            // 1. 创建公司
            $company = new Company();
            $company->company_name = $companyName;
            $company->app_name = $companyName;
            $company->company_email = $adminEmail;
            $company->company_phone = '1234567890';
            $company->address = 'Test Address';
            $company->website = 'https://testcompany.com';
            $company->date_format = 'd-m-Y';
            $company->time_format = 'h:i A';
            $company->currency_id = 1; // 默认货币
            $company->timezone = 'Asia/Shanghai';
            $company->save();
            
            $this->command->info("公司创建成功，ID: {$company->id}");
            
            // 2. 创建公司地址
            $companyAddress = new CompanyAddress();
            $companyAddress->company_id = $company->id;
            $companyAddress->address = 'Test Address';
            $companyAddress->save();
            
            // 3. 创建管理员用户认证
            $userAuth = new UserAuth();
            $userAuth->email = $adminEmail;
            $userAuth->password = bcrypt($adminPassword);
            $userAuth->save();
            
            $this->command->info("用户认证创建成功，ID: {$userAuth->id}");
            
            // 4. 创建管理员用户
            $user = new User();
            $user->name = 'Admin User';
            $user->email = $adminEmail;
            $user->company_id = $company->id;
            $user->user_auth_id = $userAuth->id;
            $user->gender = 'male';
            $user->save();
            
            $this->command->info("用户创建成功，ID: {$user->id}");
            
            // 5. 创建员工详情
            $employeeDetails = new EmployeeDetails();
            $employeeDetails->user_id = $user->id;
            $employeeDetails->company_id = $company->id;
            $employeeDetails->employee_id = 'EMP001';
            $employeeDetails->designation_id = 1; // 默认职位
            $employeeDetails->department_id = 1; // 默认部门
            $employeeDetails->save();
            
            // 6. 获取或创建管理员角色
            $adminRole = Role::where('name', 'admin')->where('company_id', $company->id)->first();
            
            if (!$adminRole) {
                $adminRole = new Role();
                $adminRole->name = 'admin';
                $adminRole->display_name = 'Admin';
                $adminRole->company_id = $company->id;
                $adminRole->save();
                
                $this->command->info("管理员角色创建成功，ID: {$adminRole->id}");
                
                // 为管理员角色分配所有权限
                $permissions = Permission::all();
                if ($permissions->count() > 0) {
                    $adminRole->permissions()->attach($permissions->pluck('id'));
                    $this->command->info("权限分配成功，共分配 {$permissions->count()} 个权限");
                }
            }
            
            // 7. 为用户分配管理员角色
            $user->roles()->attach($adminRole->id);
            $this->command->info("用户角色分配成功");
            
            DB::commit();
            
            $this->command->info("\n=== 公司创建成功 ===");
            $this->command->info("公司ID: {$company->id}");
            $this->command->info("公司名称: {$company->company_name}");
            $this->command->info("管理员邮箱: {$adminEmail}");
            $this->command->info("管理员密码: {$adminPassword}");
            $this->command->info("===================");
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error("创建失败: " . $e->getMessage());
            $this->command->error("错误位置: " . $e->getFile() . ":" . $e->getLine());
        }
    }
}
