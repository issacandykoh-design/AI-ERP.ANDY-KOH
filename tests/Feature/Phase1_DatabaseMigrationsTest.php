<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tests\Helpers\DatabaseTestHelper;

class Phase1_DatabaseMigrationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DatabaseTestHelper::createTestTables();
    }

    /**
     * Test that all new marketplace and AI credit tables exist
     */
    public function test_marketplace_modules_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('marketplace_modules'),
            'marketplace_modules table should exist'
        );
    }

    public function test_module_categories_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('module_categories'),
            'module_categories table should exist'
        );
    }

    public function test_company_module_subscriptions_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('company_module_subscriptions'),
            'company_module_subscriptions table should exist'
        );
    }

    public function test_module_dependencies_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('module_dependencies'),
            'module_dependencies table should exist'
        );
    }

    public function test_ai_credit_packages_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('ai_credit_packages'),
            'ai_credit_packages table should exist'
        );
    }

    public function test_ai_credit_promotions_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('ai_credit_promotions'),
            'ai_credit_promotions table should exist'
        );
    }

    public function test_company_ai_credits_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('company_ai_credits'),
            'company_ai_credits table should exist'
        );
    }

    public function test_ai_credit_transactions_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('ai_credit_transactions'),
            'ai_credit_transactions table should exist'
        );
    }

    public function test_ai_credit_purchases_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('ai_credit_purchases'),
            'ai_credit_purchases table should exist'
        );
    }

    public function test_ai_usage_logs_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('ai_usage_logs'),
            'ai_usage_logs table should exist'
        );
    }

    public function test_package_ai_credits_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('package_ai_credits'),
            'package_ai_credits table should exist'
        );
    }

    /**
     * Test that existing tables have new columns
     */
    public function test_companies_table_has_ai_credits_balance(): void
    {
        $this->assertTrue(
            Schema::hasColumn('companies', 'ai_credits_balance'),
            'companies table should have ai_credits_balance column'
        );
    }

    public function test_packages_table_has_is_module_addon(): void
    {
        $this->assertTrue(
            Schema::hasColumn('packages', 'is_module_addon'),
            'packages table should have is_module_addon column'
        );
    }

    /**
     * Test table columns have correct types
     */
    public function test_marketplace_modules_has_required_columns(): void
    {
        $columns = Schema::getColumnListing('marketplace_modules');

        $this->assertContains('id', $columns);
        $this->assertContains('module_key', $columns);
        $this->assertContains('name', $columns);
        $this->assertContains('price_monthly', $columns);
        $this->assertContains('price_annual', $columns);
        $this->assertContains('is_active', $columns);
        $this->assertContains('is_featured', $columns);
    }

    public function test_company_ai_credits_has_required_columns(): void
    {
        $columns = Schema::getColumnListing('company_ai_credits');

        $this->assertContains('id', $columns);
        $this->assertContains('company_id', $columns);
        $this->assertContains('balance', $columns);
        $this->assertContains('lifetime_earned', $columns);
        $this->assertContains('lifetime_spent', $columns);
    }
}

