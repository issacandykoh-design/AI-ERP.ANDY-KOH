# AI F&B Module - Implementation Guide
## Following System Business Logic & Module Architecture

---

## Executive Summary

This guide shows how to build the AI F&B features as a **proper module** following the existing system's business logic, including package integration, module settings, permissions, and SuperAdmin configuration.

---

## 1. MODULE SYSTEM ARCHITECTURE

### 1.1 How Modules Work in This System

**Module Flow:**
```
1. Module exists in Modules/ directory
   ↓
2. module.json defines module metadata
   ↓
3. ModuleSwitch table controls global enable/disable
   ↓
4. Package has module_in_package (JSON array)
   ↓
5. CompanyObserver creates ModuleSetting based on package
   ↓
6. ModuleSetting controls per-company/per-role access
   ↓
7. user_modules() function returns enabled modules
   ↓
8. Menu/features show based on enabled modules
```

### 1.2 Key Components

**1. ModuleSwitch (Global)**
- Controls if module is enabled system-wide
- SuperAdmin can enable/disable modules
- Stored in `module_switches` table

**2. Package (module_in_package)**
- Package has JSON array: `["purchase", "ai_fnb", "products"]`
- Defines which modules are available in package

**3. ModuleSetting (Per-Company/Per-Role)**
- Created by CompanyObserver when company gets package
- Controls access per role (admin, employee, client)
- Based on package's `module_in_package`

**4. Permissions**
- Defined in `Module::MODULE_LIST`
- Controls granular permissions per role

---

## 2. MODULE STRUCTURE

### 2.1 Directory Structure

```
Modules/AIFnb/
├── Config/
│   ├── config.php
│   └── xss_ignore.php
├── Console/
│   └── ActivateModuleCommand.php
├── Database/
│   ├── Migrations/
│   │   ├── 2024_01_01_000001_create_ai_recipes_table.php
│   │   ├── 2024_01_01_000002_create_ai_nutrition_facts_table.php
│   │   ├── 2024_01_01_000003_create_ai_subscription_tiers_table.php
│   │   ├── 2024_01_01_000004_create_company_ai_subscriptions_table.php
│   │   └── 2024_01_01_000005_create_ai_usage_logs_table.php
│   └── Seeders/
│       └── AIFnbModuleSeeder.php
├── Entities/
│   ├── AIRecipe.php
│   ├── AINutritionFact.php
│   ├── AISubscriptionTier.php
│   ├── CompanyAISubscription.php
│   └── AIUsageLog.php
├── Events/
│   ├── RecipeGenerated.php
│   └── NutritionFactGenerated.php
├── Http/
│   ├── Controllers/
│   │   ├── AIRecipeController.php
│   │   ├── AINutritionController.php
│   │   ├── AISubscriptionController.php
│   │   └── AIUsageController.php
│   └── Requests/
│       ├── GenerateRecipeRequest.php
│       └── GenerateNutritionRequest.php
├── Listeners/
│   ├── RecipeGeneratedListener.php
│   └── CompanyCreatedListener.php
├── Notifications/
│   └── RecipeGeneratedNotification.php
├── Observers/
│   ├── AIRecipeObserver.php
│   └── CompanyAISubscriptionObserver.php
├── Providers/
│   ├── AIFnbServiceProvider.php
│   ├── RouteServiceProvider.php
│   └── EventServiceProvider.php
├── Resources/
│   ├── lang/
│   │   └── en/
│   │       └── modules.php
│   └── views/
│       ├── recipes/
│       ├── nutrition/
│       └── subscriptions/
├── Routes/
│   ├── web.php
│   └── api.php
├── Services/
│   ├── OpenRouterService.php
│   ├── RecipeGenerationService.php
│   ├── NutritionCalculationService.php
│   └── AICreditService.php
├── Helper/
│   └── start.php
├── module.json
├── start.php
└── version.txt
```

---

## 3. MODULE.JSON CONFIGURATION

### 3.1 module.json File

```json
{
    "name": "AI F&B",
    "alias": "ai_fnb",
    "description": "AI-powered recipe generation, cooking instructions, and nutrition facts for F&B companies",
    "keywords": ["ai", "recipe", "nutrition", "food", "beverage"],
    "version": "1.0.0",
    "active": true,
    "order": 10,
    "priority": 0,
    "providers": [
        "Modules\\AIFnb\\Providers\\AIFnbServiceProvider",
        "Modules\\AIFnb\\Providers\\EventServiceProvider"
    ],
    "aliases": [],
    "files": [
        "Helper/start.php"
    ],
    "parent_product_name": "Craveva",
    "parent_min_version": "1.0.0"
}
```

---

## 4. SERVICE PROVIDER

### 4.1 AIFnbServiceProvider.php

```php
<?php

namespace Modules\AIFnb\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AIFnb\Console\ActivateModuleCommand;
use Modules\AIFnb\Providers\RouteServiceProvider;

class AIFnbServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerCommands();
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        
        // Register services
        $this->app->singleton('ai_fnb.openrouter', function ($app) {
            return new \Modules\AIFnb\Services\OpenRouterService();
        });
        
        $this->app->singleton('ai_fnb.recipe', function ($app) {
            return new \Modules\AIFnb\Services\RecipeGenerationService();
        });
        
        $this->app->singleton('ai_fnb.nutrition', function ($app) {
            return new \Modules\AIFnb\Services\NutritionCalculationService();
        });
        
        $this->app->singleton('ai_fnb.credits', function ($app) {
            return new \Modules\AIFnb\Services\AICreditService();
        });
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('ai_fnb.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'ai_fnb'
        );
    }

    public function registerViews(): void
    {
        $viewPath = base_path('resources/views/modules/ai_fnb');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom([$sourcePath], 'ai_fnb');
    }

    public function registerTranslations(): void
    {
        $langPath = base_path('resources/lang/modules/ai_fnb');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'ai_fnb');
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'ai_fnb');
        }
    }

    private function registerCommands(): void
    {
        $this->commands([
            ActivateModuleCommand::class,
        ]);
    }
}
```

---

## 5. MODULE REGISTRATION IN SYSTEM

### 5.1 Add to Module Model

**File: `app/Models/Module.php`**

Add to `MODULE_LIST` constant:

```php
[
    'module_name' => 'ai_fnb',
    'permissions' => [
        [
            'allowed_permissions' => Permission::ALL_NONE,
            'is_custom' => 0,
            'name' => 'add_ai_recipe',
        ],
        [
            'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
            'is_custom' => 0,
            'name' => 'view_ai_recipe',
        ],
        [
            'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
            'is_custom' => 0,
            'name' => 'edit_ai_recipe',
        ],
        [
            'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
            'is_custom' => 0,
            'name' => 'delete_ai_recipe',
        ],
        [
            'allowed_permissions' => Permission::ALL_NONE,
            'is_custom' => 1,
            'name' => 'generate_ai_recipe',
        ],
        [
            'allowed_permissions' => Permission::ALL_NONE,
            'is_custom' => 1,
            'name' => 'generate_ai_nutrition',
        ],
        [
            'allowed_permissions' => Permission::ALL_NONE,
            'is_custom' => 1,
            'name' => 'generate_ai_images',
        ],
        [
            'allowed_permissions' => Permission::ALL_NONE,
            'is_custom' => 1,
            'name' => 'generate_ai_videos',
        ],
        [
            'allowed_permissions' => Permission::ALL_NONE,
            'is_custom' => 1,
            'name' => 'manage_ai_subscription',
        ],
        [
            'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
            'is_custom' => 1,
            'name' => 'view_ai_usage',
        ],
    ]
],
```

### 5.2 Add to ModuleSetting Constants

**File: `app/Models/ModuleSetting.php`**

Add to appropriate constant arrays:

```php
const OTHER_MODULES = [
    'clients',
    'employees',
    'attendance',
    'expenses',
    'leaves',
    'leads',
    'holidays',
    'products',
    'reports',
    'settings',
    'bankaccount',
    'ai_fnb', // Add here
];
```

---

## 6. PACKAGE INTEGRATION

### 6.1 Package Configuration

**SuperAdmin creates/edits packages:**
- Package has `module_in_package` field (JSON)
- Add `"ai_fnb"` to the JSON array
- Example: `["products", "orders", "ai_fnb", "purchase"]`

### 6.2 CompanyObserver Integration

**File: `app/Observers/CompanyObserver.php`**

The observer already handles module settings based on package. It will automatically:
1. Read `package->module_in_package`
2. Create `ModuleSetting` records for each module
3. Set `is_allowed=1` and `status='active'` for modules in package
4. Set `is_allowed=0` and `status='deactive'` for modules NOT in package

**No changes needed** - it works automatically!

---

## 7. MODULE SWITCH INTEGRATION

### 7.1 ModuleSwitch Seeder

**File: `database/seeders/ModuleSwitchSeeder.php`**

The seeder automatically:
1. Scans `Modules/` directory
2. Reads `module.json` files
3. Creates/updates `ModuleSwitch` records
4. Sets `is_enabled` based on `module.json` `active` field

**No changes needed** - it works automatically!

### 7.2 SuperAdmin Module Switch UI

**Route:** `/super-admin/module-switch`

SuperAdmin can:
- Enable/disable modules globally
- View module status
- See module version

**No changes needed** - it works automatically!

---

## 8. PERMISSIONS SYSTEM

### 8.1 Permission Check Helper

**Usage in Controllers:**

```php
use App\Helper\Reply;

class AIRecipeController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'AI Recipe Generator';
        
        // Check module is enabled
        abort_403(!module_enabled('ai_fnb'));
        
        // Check user has permission
        abort_403(!in_array('ai_fnb', user_modules()));
        
        // Check specific permission
        abort_403(user()->permission('generate_ai_recipe') == 'none');
    }
    
    public function generate(Request $request)
    {
        // Permission check
        abort_403(user()->permission('generate_ai_recipe') == 'none');
        
        // Check credits
        $subscription = CompanyAISubscription::where('company_id', company()->id)
            ->where('status', 'active')
            ->first();
            
        if (!$subscription || $subscription->current_credits < 5) {
            return Reply::error('Insufficient AI credits. Please upgrade your subscription.');
        }
        
        // Generate recipe...
    }
}
```

---

## 9. ROUTES

### 9.1 Web Routes

**File: `Modules/AIFnb/Routes/web.php`**

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\AIFnb\Http\Controllers\AIRecipeController;
use Modules\AIFnb\Http\Controllers\AINutritionController;
use Modules\AIFnb\Http\Controllers\AISubscriptionController;

Route::group(['middleware' => ['auth', 'check_company']], function () {
    
    // Check module enabled
    Route::group(['middleware' => ['module_enabled:ai_fnb']], function () {
        
        // Recipe Generation
        Route::prefix('ai-fnb/recipes')->group(function () {
            Route::get('/', [AIRecipeController::class, 'index'])->name('ai_fnb.recipes.index');
            Route::get('/create', [AIRecipeController::class, 'create'])->name('ai_fnb.recipes.create');
            Route::post('/generate', [AIRecipeController::class, 'generate'])->name('ai_fnb.recipes.generate');
            Route::get('/{id}', [AIRecipeController::class, 'show'])->name('ai_fnb.recipes.show');
            Route::post('/{id}/approve', [AIRecipeController::class, 'approve'])->name('ai_fnb.recipes.approve');
            Route::delete('/{id}', [AIRecipeController::class, 'destroy'])->name('ai_fnb.recipes.destroy');
        });
        
        // Nutrition Facts
        Route::prefix('ai-fnb/nutrition')->group(function () {
            Route::post('/generate', [AINutritionController::class, 'generate'])->name('ai_fnb.nutrition.generate');
            Route::get('/{id}', [AINutritionController::class, 'show'])->name('ai_fnb.nutrition.show');
        });
        
        // Subscriptions
        Route::prefix('ai-fnb/subscription')->group(function () {
            Route::get('/', [AISubscriptionController::class, 'index'])->name('ai_fnb.subscription.index');
            Route::post('/upgrade', [AISubscriptionController::class, 'upgrade'])->name('ai_fnb.subscription.upgrade');
        });
    });
});
```

### 9.2 RouteServiceProvider

**File: `Modules/AIFnb/Providers/RouteServiceProvider.php`**

```php
<?php

namespace Modules\AIFnb\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleName = 'AIFnb';
    protected $moduleNameLower = 'ai_fnb';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path($this->moduleName, '/Routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(module_path($this->moduleName, '/Routes/api.php'));
    }
}
```

---

## 10. SUPERADMIN CONFIGURATION

### 10.1 SuperAdmin AI Settings

**New Controller:** `app/Http/Controllers/SuperAdmin/AIFnbSettingsController.php`

```php
<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use App\Helper\Reply;

class AIFnbSettingsController extends AccountBaseController
{
    public function index()
    {
        $this->pageTitle = 'AI F&B Settings';
        
        // Get OpenRouter settings
        $this->openRouterApiKey = config('ai_fnb.openrouter_api_key');
        $this->defaultModels = config('ai_fnb.models');
        $this->creditPricing = config('ai_fnb.credit_pricing');
        
        return view('super-admin.ai-fnb-settings.index', $this->data);
    }
    
    public function update(Request $request)
    {
        // Update config
        // Store in database or config file
        // Update cache
        
        return Reply::success('Settings updated successfully');
    }
}
```

**New Route:** `routes/super-admin.php`

```php
Route::prefix('super-admin')->group(function () {
    Route::get('/ai-fnb-settings', [AIFnbSettingsController::class, 'index'])
        ->name('super-admin.ai-fnb-settings.index');
    Route::post('/ai-fnb-settings', [AIFnbSettingsController::class, 'update'])
        ->name('super-admin.ai-fnb-settings.update');
});
```

### 10.2 SuperAdmin Permission

**Add to Module Model - SUPERADMIN_MODULE_LIST:**

```php
[
    'module_name' => 'ai_fnb_settings',
    'is_superadmin' => 1,
    'permissions' => [
        [
            'allowed_permissions' => Permission::ALL_NONE,
            'is_custom' => 1,
            'name' => 'manage_ai_fnb_settings',
        ],
    ],
],
```

---

## 11. MENU INTEGRATION

### 11.1 Add to Sidebar Menu

**File: `resources/views/sections/menu.blade.php`**

Add menu item (check existing pattern):

```blade
@if(in_array('ai_fnb', user_modules()))
    <x-menu-item 
        :text="__('app.menu.aiFnb')" 
        icon="icon-robot"
        module="ai_fnb"
        :url="route('ai_fnb.recipes.index')"
    />
@endif
```

### 11.2 Language File

**File: `resources/lang/en/app.php`**

Add translation:

```php
'menu' => [
    // ... existing ...
    'aiFnb' => 'AI F&B',
],
```

---

## 12. DATABASE MIGRATIONS

### 12.1 Migration Structure

**Example Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cASCADE');
            $table->unsignedInteger('product_id')->nullable()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('SET NULL');
            $table->string('recipe_name');
            $table->text('recipe_description')->nullable();
            // ... other fields ...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recipes');
    }
};
```

---

## 13. MODULE ACTIVATION COMMAND

### 13.1 ActivateModuleCommand

**File: `Modules/AIFnb/Console/ActivateModuleCommand.php`**

```php
<?php

namespace Modules\AIFnb\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ActivateModuleCommand extends Command
{
    protected $signature = 'ai-fnb:activate';
    protected $description = 'Activate AI F&B module';

    public function handle(): int
    {
        $this->info('Activating AI F&B module...');
        
        // Run migrations
        Artisan::call('migrate', ['--path' => 'Modules/AIFnb/Database/Migrations']);
        
        // Seed default data
        Artisan::call('db:seed', ['--class' => 'Modules\\AIFnb\\Database\\Seeders\\AIFnbModuleSeeder']);
        
        // Clear cache
        Artisan::call('optimize:clear');
        
        $this->info('AI F&B module activated successfully!');
        
        return 0;
    }
}
```

---

## 14. PACKAGE MODULE ASSIGNMENT

### 14.1 How Packages Control Modules

**Package Table Structure:**
```sql
packages
├── id
├── name
├── module_in_package (json) - ["products", "orders", "ai_fnb"]
└── ...
```

**SuperAdmin assigns modules to packages:**
1. Go to Packages page
2. Edit package
3. Select modules (including "ai_fnb")
4. Save package
5. `module_in_package` JSON updated

**Company gets package:**
1. Company assigned to package
2. CompanyObserver fires
3. Reads `package->module_in_package`
4. Creates `ModuleSetting` records
5. Module becomes available to company

---

## 15. MODULE CHECK HELPER

### 15.1 Usage in Code

**Check if module enabled:**
```php
if (!module_enabled('ai_fnb')) {
    return Reply::error('AI F&B module is not enabled');
}
```

**Check if user has access:**
```php
if (!in_array('ai_fnb', user_modules())) {
    abort_403();
}
```

**Check permission:**
```php
if (user()->permission('generate_ai_recipe') == 'none') {
    abort_403();
}
```

---

## 16. IMPLEMENTATION STEPS

### Step 1: Create Module Structure
```bash
php artisan module:make AIFnb
```

### Step 2: Create module.json
- Copy from Purchase module
- Update name, alias, description

### Step 3: Create ServiceProvider
- Register services
- Register routes
- Register migrations

### Step 4: Create Migrations
- AI recipes table
- Nutrition facts table
- Subscription tables
- Usage logs table

### Step 5: Create Models/Entities
- AIRecipe
- AINutritionFact
- CompanyAISubscription
- etc.

### Step 6: Create Controllers
- AIRecipeController
- AINutritionController
- AISubscriptionController

### Step 7: Create Services
- OpenRouterService
- RecipeGenerationService
- NutritionCalculationService
- AICreditService

### Step 8: Add to Module Model
- Add to MODULE_LIST
- Add permissions

### Step 9: Add to ModuleSetting
- Add to OTHER_MODULES constant

### Step 10: Create Routes
- Web routes
- API routes

### Step 11: Create Views
- Recipe generation UI
- Nutrition facts UI
- Subscription management UI

### Step 12: Add Menu Item
- Add to sidebar menu
- Add translations

### Step 13: Create SuperAdmin Settings
- AI configuration page
- Model selection
- Pricing management

### Step 14: Test Module
- Enable module
- Assign to package
- Test permissions
- Test features

---

## 17. SUMMARY

### Module Integration Points:

1. ✅ **ModuleSwitch** - Global enable/disable (automatic)
2. ✅ **Package** - Add to `module_in_package` JSON
3. ✅ **ModuleSetting** - Auto-created by CompanyObserver
4. ✅ **Permissions** - Defined in Module::MODULE_LIST
5. ✅ **Menu** - Check `user_modules()` function
6. ✅ **Routes** - Protected by `module_enabled` middleware
7. ✅ **SuperAdmin** - Settings page for configuration

### Business Logic Flow:

```
SuperAdmin enables module in ModuleSwitch
   ↓
SuperAdmin adds "ai_fnb" to package's module_in_package
   ↓
Company gets package
   ↓
CompanyObserver creates ModuleSetting records
   ↓
user_modules() returns ["ai_fnb", ...]
   ↓
Menu shows AI F&B option
   ↓
User can access AI features
   ↓
Permissions control granular access
```

---

**This module will integrate seamlessly with the existing system following all business logic patterns!**

