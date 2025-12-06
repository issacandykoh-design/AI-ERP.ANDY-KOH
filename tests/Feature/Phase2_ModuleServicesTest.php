<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\MarketplaceModule;
use App\Models\CompanyModuleSubscription;
use App\Models\ModuleCategory;
use App\Models\ModuleDependency;
use App\Models\SuperAdmin\Package;
use App\Services\ModuleMarketplaceService;
use App\Services\ModuleAddonService;
use Tests\TestCase;
use Tests\Helpers\DatabaseTestHelper;

class Phase2_ModuleServicesTest extends TestCase
{
    protected ModuleMarketplaceService $marketplaceService;
    protected ModuleAddonService $addonService;

    protected function setUp(): void
    {
        parent::setUp();
        DatabaseTestHelper::createTestTables();
        $this->marketplaceService = new ModuleMarketplaceService();
        $this->addonService = new ModuleAddonService();
    }

    /**
     * Test ModuleMarketplaceService
     */
    public function test_get_active_modules_returns_only_active(): void
    {
        MarketplaceModule::create([
            'module_key' => 'active_module',
            'name' => 'Active Module',
            'is_active' => true,
        ]);

        MarketplaceModule::create([
            'module_key' => 'inactive_module',
            'name' => 'Inactive Module',
            'is_active' => false,
        ]);

        $modules = $this->marketplaceService->getActiveModules();

        $this->assertCount(1, $modules);
        $this->assertEquals('active_module', $modules->first()->module_key);
    }

    public function test_get_featured_modules_returns_featured_only(): void
    {
        MarketplaceModule::create([
            'module_key' => 'featured1',
            'name' => 'Featured 1',
            'is_active' => true,
            'is_featured' => true,
        ]);

        MarketplaceModule::create([
            'module_key' => 'normal1',
            'name' => 'Normal 1',
            'is_active' => true,
            'is_featured' => false,
        ]);

        $featured = $this->marketplaceService->getFeaturedModules();

        $this->assertCount(1, $featured);
        $this->assertEquals('featured1', $featured->first()->module_key);
    }

    public function test_has_access_checks_package_modules(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create([
            'module_in_package' => json_encode(['test_module']),
        ]);
        $company->update(['package_id' => $package->id]);

        $module = MarketplaceModule::create([
            'module_key' => 'test_module',
            'name' => 'Test Module',
            'is_active' => true,
        ]);

        $this->assertTrue($this->marketplaceService->hasAccess($company, $module));
    }

    public function test_has_access_checks_subscription(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create();
        $company->update(['package_id' => $package->id]);

        $module = MarketplaceModule::create([
            'module_key' => 'test_module',
            'name' => 'Test Module',
            'is_active' => true,
        ]);

        CompanyModuleSubscription::create([
            'company_id' => $company->id,
            'package_id' => $package->id,
            'marketplace_module_id' => $module->id,
            'subscription_type' => 'monthly',
            'status' => 'active',
        ]);

        $this->assertTrue($this->marketplaceService->hasAccess($company, $module));
    }

    /**
     * Test ModuleAddonService
     */
    public function test_add_module_addon_creates_subscription(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create();
        $company->update(['package_id' => $package->id]);

        $module = MarketplaceModule::create([
            'module_key' => 'test_module',
            'name' => 'Test Module',
            'price_monthly' => 10.00,
            'is_active' => true,
        ]);

        $subscription = $this->addonService->addModuleAddon($company, $module, 'monthly');

        $this->assertNotNull($subscription);
        $this->assertTrue($subscription->is_addon);
        $this->assertEquals('active', $subscription->status);
        $this->assertDatabaseHas('company_module_subscriptions', [
            'company_id' => $company->id,
            'marketplace_module_id' => $module->id,
        ]);
    }

    public function test_add_module_addon_prevents_duplicate(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create();
        $company->update(['package_id' => $package->id]);

        $module = MarketplaceModule::create([
            'module_key' => 'test_module',
            'name' => 'Test Module',
            'is_active' => true,
        ]);

        $this->addonService->addModuleAddon($company, $module, 'monthly');
        $duplicate = $this->addonService->addModuleAddon($company, $module, 'monthly');

        $this->assertNull($duplicate);
    }

    public function test_check_dependencies_validates_required_modules(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create([
            'module_in_package' => json_encode(['required_module']),
        ]);
        $company->update(['package_id' => $package->id]);

        $requiredModule = MarketplaceModule::create([
            'module_key' => 'required_module',
            'name' => 'Required Module',
            'is_active' => true,
        ]);

        $dependentModule = MarketplaceModule::create([
            'module_key' => 'dependent_module',
            'name' => 'Dependent Module',
            'is_active' => true,
        ]);

        ModuleDependency::create([
            'marketplace_module_id' => $dependentModule->id,
            'required_module_id' => $requiredModule->id,
        ]);

        $this->assertTrue($this->addonService->checkDependencies($company, $dependentModule));
    }

    public function test_remove_module_addon_cancels_subscription(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create();
        $company->update(['package_id' => $package->id]);

        $module = MarketplaceModule::create([
            'module_key' => 'test_module',
            'name' => 'Test Module',
            'is_active' => true,
        ]);

        $subscription = $this->addonService->addModuleAddon($company, $module, 'monthly');
        $result = $this->addonService->removeModuleAddon($company, $module, true);

        $this->assertTrue($result);
        $subscription->refresh();
        $this->assertEquals('cancelled', $subscription->status);
    }

    public function test_get_company_addons_returns_active_only(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create();
        $company->update(['package_id' => $package->id]);

        $module1 = MarketplaceModule::create([
            'module_key' => 'module1',
            'name' => 'Module 1',
            'is_active' => true,
        ]);

        $module2 = MarketplaceModule::create([
            'module_key' => 'module2',
            'name' => 'Module 2',
            'is_active' => true,
        ]);

        $this->addonService->addModuleAddon($company, $module1, 'monthly');
        $this->addonService->addModuleAddon($company, $module2, 'monthly');

        $addons = $this->addonService->getCompanyAddons($company);

        $this->assertCount(2, $addons);
    }
}

