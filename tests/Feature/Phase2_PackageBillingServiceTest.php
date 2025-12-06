<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\MarketplaceModule;
use App\Models\AICreditPackage;
use App\Models\SuperAdmin\Package;
use App\Services\PackageBillingService;
use App\Services\AICreditsService;
use App\Services\ModuleAddonService;
use Tests\TestCase;
use Tests\Helpers\DatabaseTestHelper;

class Phase2_PackageBillingServiceTest extends TestCase
{
    protected PackageBillingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        DatabaseTestHelper::createTestTables();
        $this->service = new PackageBillingService(
            new AICreditsService(),
            new ModuleAddonService()
        );
    }

    /**
     * Test calculateBillingAmount
     */
    public function test_calculate_billing_amount_includes_package(): void
    {
        $package = Package::factory()->create([
            'monthly_price' => 99.00,
            'annual_price' => 990.00,
        ]);

        $billing = $this->service->calculateBillingAmount($package, [], null, null, 'monthly');

        $this->assertEquals(99.00, $billing['total']);
        $this->assertCount(1, $billing['items']);
        $this->assertEquals('package', $billing['items'][0]['type']);
    }

    public function test_calculate_billing_amount_includes_addons(): void
    {
        $package = Package::factory()->create([
            'monthly_price' => 99.00,
        ]);

        $module1 = MarketplaceModule::create([
            'module_key' => 'module1',
            'name' => 'Module 1',
            'price_monthly' => 20.00,
            'is_active' => true,
        ]);

        $module2 = MarketplaceModule::create([
            'module_key' => 'module2',
            'name' => 'Module 2',
            'price_monthly' => 15.00,
            'is_active' => true,
        ]);

        $billing = $this->service->calculateBillingAmount(
            $package,
            [$module1->id, $module2->id],
            null,
            null,
            'monthly'
        );

        $this->assertEquals(134.00, $billing['total']); // 99 + 20 + 15
        $this->assertCount(3, $billing['items']);
    }

    public function test_calculate_billing_amount_includes_ai_credits(): void
    {
        $package = Package::factory()->create([
            'monthly_price' => 99.00,
        ]);

        $creditPackage = AICreditPackage::create([
            'name' => 'Starter',
            'credits_amount' => 1000,
            'price' => 50.00,
            'is_active' => true,
        ]);

        $billing = $this->service->calculateBillingAmount(
            $package,
            [],
            $creditPackage->id,
            null,
            'monthly'
        );

        $this->assertEquals(149.00, $billing['total']); // 99 + 50
        $this->assertCount(2, $billing['items']);
        $this->assertEquals('ai_credits', $billing['items'][1]['type']);
    }

    /**
     * Test createUnifiedSubscription
     */
    public function test_create_unified_subscription_creates_all_components(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create([
            'monthly_price' => 99.00,
        ]);

        $module = MarketplaceModule::create([
            'module_key' => 'test_module',
            'name' => 'Test Module',
            'price_monthly' => 20.00,
            'is_active' => true,
        ]);

        $creditPackage = AICreditPackage::create([
            'name' => 'Starter',
            'credits_amount' => 1000,
            'price' => 50.00,
            'is_active' => true,
        ]);

        $subscription = $this->service->createUnifiedSubscription(
            $company,
            $package,
            [$module->id],
            $creditPackage->id,
            null,
            'monthly'
        );

        $this->assertNotNull($subscription);
        $this->assertEquals('unified', $subscription->subscription_type);

        // Check module addon was created
        $this->assertDatabaseHas('company_module_subscriptions', [
            'company_id' => $company->id,
            'marketplace_module_id' => $module->id,
        ]);

        // Check credits were added
        $creditsService = new AICreditsService();
        $this->assertEquals(1000, $creditsService->getBalance($company));
    }

    /**
     * Test getSubscriptionSummary
     */
    public function test_get_subscription_summary_returns_all_data(): void
    {
        $company = Company::factory()->create();
        $package = Package::factory()->create();
        $company->update(['package_id' => $package->id]);

        $summary = $this->service->getSubscriptionSummary($company);

        $this->assertArrayHasKey('subscription', $summary);
        $this->assertArrayHasKey('package', $summary);
        $this->assertArrayHasKey('addons', $summary);
        $this->assertArrayHasKey('ai_credits', $summary);
        $this->assertArrayHasKey('balance', $summary['ai_credits']);
    }
}

