<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyAICredit;
use App\Models\AICreditPackage;
use App\Models\AICreditPromotion;
use App\Models\AICreditTransaction;
use App\Services\AICreditsService;
use Tests\TestCase;
use Tests\Helpers\DatabaseTestHelper;

class Phase2_AICreditsServiceTest extends TestCase
{
    protected AICreditsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        DatabaseTestHelper::createTestTables();
        $this->service = new AICreditsService();
    }

    /**
     * Test getOrCreateCompanyCredits
     */
    public function test_get_or_create_company_credits_creates_new_record(): void
    {
        $company = Company::factory()->create();

        $credits = $this->service->getOrCreateCompanyCredits($company);

        $this->assertInstanceOf(CompanyAICredit::class, $credits);
        $this->assertEquals(0, $credits->balance);
        $this->assertEquals($company->id, $credits->company_id);
    }

    public function test_get_or_create_company_credits_returns_existing(): void
    {
        $company = Company::factory()->create();
        CompanyAICredit::create([
            'company_id' => $company->id,
            'balance' => 500.00,
        ]);

        $credits = $this->service->getOrCreateCompanyCredits($company);

        $this->assertEquals(500.00, $credits->balance);
    }

    /**
     * Test getBalance
     */
    public function test_get_balance_returns_correct_balance(): void
    {
        $company = Company::factory()->create();
        CompanyAICredit::create([
            'company_id' => $company->id,
            'balance' => 750.50,
        ]);

        $balance = $this->service->getBalance($company);

        $this->assertEquals(750.50, $balance);
    }

    /**
     * Test hasSufficientCredits
     */
    public function test_has_sufficient_credits_returns_true_when_enough(): void
    {
        $company = Company::factory()->create();
        CompanyAICredit::create([
            'company_id' => $company->id,
            'balance' => 1000.00,
        ]);

        $this->assertTrue($this->service->hasSufficientCredits($company, 500.00));
        $this->assertTrue($this->service->hasSufficientCredits($company, 1000.00));
    }

    public function test_has_sufficient_credits_returns_false_when_not_enough(): void
    {
        $company = Company::factory()->create();
        CompanyAICredit::create([
            'company_id' => $company->id,
            'balance' => 100.00,
        ]);

        $this->assertFalse($this->service->hasSufficientCredits($company, 500.00));
    }

    /**
     * Test addCredits
     */
    public function test_add_credits_updates_balance(): void
    {
        $company = Company::factory()->create();
        CompanyAICredit::create([
            'company_id' => $company->id,
            'balance' => 100.00,
        ]);

        $result = $this->service->addCredits($company, 500.00, 'Test addition');

        $this->assertTrue($result);
        $this->assertEquals(600.00, $this->service->getBalance($company));

        // Check transaction was created
        $this->assertDatabaseHas('ai_credit_transactions', [
            'company_id' => $company->id,
            'amount' => 500.00,
            'transaction_type' => 'purchase',
        ]);
    }

    /**
     * Test deductCredits
     */
    public function test_deduct_credits_updates_balance(): void
    {
        $company = Company::factory()->create();
        CompanyAICredit::create([
            'company_id' => $company->id,
            'balance' => 1000.00,
        ]);

        $result = $this->service->deductCredits($company, 300.00, 'Test deduction');

        $this->assertTrue($result);
        $this->assertEquals(700.00, $this->service->getBalance($company));
    }

    public function test_deduct_credits_fails_when_insufficient(): void
    {
        $company = Company::factory()->create();
        CompanyAICredit::create([
            'company_id' => $company->id,
            'balance' => 100.00,
        ]);

        $result = $this->service->deductCredits($company, 500.00, 'Test deduction');

        $this->assertFalse($result);
        $this->assertEquals(100.00, $this->service->getBalance($company));

        // Check exhaustion log was created
        $this->assertDatabaseHas('ai_credit_exhaustion_logs', [
            'company_id' => $company->id,
            'required_credits' => 500.00,
            'available_credits' => 100.00,
        ]);
    }

    /**
     * Test purchaseCredits
     */
    public function test_purchase_credits_adds_to_balance(): void
    {
        $company = Company::factory()->create();
        $package = AICreditPackage::create([
            'name' => 'Test Package',
            'credits_amount' => 1000,
            'price' => 50.00,
            'is_active' => true,
        ]);

        $purchase = $this->service->purchaseCredits($company, $package);

        $this->assertNotNull($purchase);
        $this->assertEquals(1000, $this->service->getBalance($company));
        $this->assertDatabaseHas('ai_credit_purchases', [
            'company_id' => $company->id,
            'ai_credit_package_id' => $package->id,
        ]);
    }

    public function test_purchase_credits_with_promotion_adds_bonus(): void
    {
        $company = Company::factory()->create();
        $package = AICreditPackage::create([
            'name' => 'Test Package',
            'credits_amount' => 1000,
            'price' => 100.00,
            'is_active' => true,
        ]);

        $promotion = AICreditPromotion::create([
            'name' => 'Test Promotion',
            'bonus_credits_amount' => 200,
            'minimum_purchase_amount' => 50.00,
            'is_active' => true,
        ]);

        $purchase = $this->service->purchaseCredits($company, $package, $promotion);

        $this->assertNotNull($purchase);
        $this->assertEquals(1200, $this->service->getBalance($company)); // 1000 + 200 bonus
        $this->assertEquals(200, $purchase->bonus_credits_amount);
    }

    /**
     * Test isPromotionEligible
     */
    public function test_is_promotion_eligible_checks_all_criteria(): void
    {
        $company = Company::factory()->create();
        $promotion = AICreditPromotion::create([
            'name' => 'Test Promotion',
            'bonus_credits_amount' => 100,
            'minimum_purchase_amount' => 50.00,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertTrue($this->service->isPromotionEligible($company, $promotion, 100.00));
        $this->assertFalse($this->service->isPromotionEligible($company, $promotion, 30.00)); // Below minimum
    }

    /**
     * Test getTransactionHistory
     */
    public function test_get_transaction_history_returns_transactions(): void
    {
        $company = Company::factory()->create();
        CompanyAICredit::create(['company_id' => $company->id]);

        $this->service->addCredits($company, 100.00, 'Test 1');
        $this->service->addCredits($company, 200.00, 'Test 2');
        $this->service->deductCredits($company, 50.00, 'Test 3');

        $history = $this->service->getTransactionHistory($company, 10);

        $this->assertCount(3, $history);
        $this->assertEquals('Test 3', $history->first()->description); // Most recent first
    }
}

