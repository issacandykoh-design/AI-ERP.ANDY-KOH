<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\MarketplaceModule;
use App\Models\CompanyModuleSubscription;
use App\Models\CompanyAICredit;
use App\Models\AICreditPackage;
use App\Models\AICreditPurchase;
use App\Models\AICreditTransaction;
use App\Models\AIUsageLog;
use App\Models\ModuleCategory;
use App\Models\SuperAdmin\Package;
use Tests\TestCase;
use Tests\Helpers\DatabaseTestHelper;

class Phase1_ModelsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DatabaseTestHelper::createTestTables();
    }

    /**
     * Test MarketplaceModule model CRUD operations
     */
    public function test_can_create_marketplace_module(): void
    {
        $module = MarketplaceModule::create([
            'module_key' => 'test_module',
            'name' => 'Test Module',
            'description' => 'Test Description',
            'price_monthly' => 10.00,
            'price_annual' => 100.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('marketplace_modules', [
            'module_key' => 'test_module',
            'name' => 'Test Module',
        ]);

        $this->assertEquals('test_module', $module->module_key);
        $this->assertEquals(10.00, $module->price_monthly);
    }

    public function test_marketplace_module_has_category_relationship(): void
    {
        $category = ModuleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $module = MarketplaceModule::create([
            'module_key' => 'test_module',
            'name' => 'Test Module',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->assertNotNull($module->category);
        $this->assertEquals('Test Category', $module->category->name);
    }

    /**
     * Test CompanyAICredit model
     */
    public function test_can_create_company_ai_credits(): void
    {
        $company = Company::factory()->create();

        $credits = CompanyAICredit::create([
            'company_id' => $company->id,
            'balance' => 1000.00,
            'lifetime_earned' => 2000.00,
            'lifetime_spent' => 1000.00,
        ]);

        $this->assertDatabaseHas('company_ai_credits', [
            'company_id' => $company->id,
            'balance' => 1000.00,
        ]);

        $this->assertEquals(1000.00, $credits->balance);
    }

    public function test_company_has_ai_credits_relationship(): void
    {
        $company = Company::factory()->create();

        CompanyAICredit::create([
            'company_id' => $company->id,
            'balance' => 500.00,
        ]);

        $this->assertNotNull($company->aiCredits);
        $this->assertEquals(500.00, $company->aiCredits->balance);
    }

    /**
     * Test CompanyModuleSubscription model
     */
    public function test_can_create_module_subscription(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create();
        $module = MarketplaceModule::create([
            'module_key' => 'test_module',
            'name' => 'Test Module',
            'is_active' => true,
        ]);

        $subscription = CompanyModuleSubscription::create([
            'company_id' => $company->id,
            'package_id' => $package->id,
            'marketplace_module_id' => $module->id,
            'subscription_type' => 'monthly',
            'status' => 'active',
            'is_addon' => true,
        ]);

        $this->assertDatabaseHas('company_module_subscriptions', [
            'company_id' => $company->id,
            'marketplace_module_id' => $module->id,
            'status' => 'active',
        ]);

        $this->assertTrue($subscription->is_addon);
    }

    public function test_company_has_module_subscriptions_relationship(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create();
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

        $this->assertCount(1, $company->moduleSubscriptions);
    }

    /**
     * Test AICreditPackage model
     */
    public function test_can_create_ai_credit_package(): void
    {
        $package = AICreditPackage::create([
            'name' => 'Starter Pack',
            'credits_amount' => 1000,
            'price' => 50.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ai_credit_packages', [
            'name' => 'Starter Pack',
            'credits_amount' => 1000,
        ]);

        $this->assertEquals(1000, $package->credits_amount);
    }

    /**
     * Test AICreditTransaction model
     */
    public function test_can_create_ai_credit_transaction(): void
    {
        $company = Company::factory()->create();

        $transaction = AICreditTransaction::create([
            'company_id' => $company->id,
            'transaction_type' => 'purchase',
            'amount' => 1000.00,
            'balance_after' => 1000.00,
            'description' => 'Test purchase',
        ]);

        $this->assertDatabaseHas('ai_credit_transactions', [
            'company_id' => $company->id,
            'transaction_type' => 'purchase',
            'amount' => 1000.00,
        ]);
    }

    /**
     * Test Package model has packageAICredits relationship
     */
    public function test_package_has_ai_credits_relationship(): void
    {
        $package = Package::factory()->create();

        \App\Models\PackageAICredit::create([
            'package_id' => $package->id,
            'credits_per_month' => 500,
        ]);

        $this->assertCount(1, $package->packageAICredits);
    }
}

