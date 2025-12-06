<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\User;
use App\Models\AIUsageLog;
use App\Models\AIUsageByModule;
use App\Models\AIUsageByContentType;
use App\Models\AIUsageSummary;
use App\Services\AIUsageTrackingService;
use Tests\TestCase;
use Tests\Helpers\DatabaseTestHelper;

class Phase2_AIUsageTrackingServiceTest extends TestCase
{
    protected AIUsageTrackingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        DatabaseTestHelper::createTestTables();
        $this->service = new AIUsageTrackingService();
    }

    /**
     * Test logUsage
     */
    public function test_log_usage_creates_log_entry(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $log = $this->service->logUsage(
            $company,
            $user,
            'invoices',
            'text',
            'translation',
            'gpt-3.5-turbo',
            100,
            50,
            1.5,
            0.002,
            ['input' => 'test'],
            ['output' => 'translated'],
            500,
            'success'
        );

        $this->assertInstanceOf(AIUsageLog::class, $log);
        $this->assertEquals('invoices', $log->module_name);
        $this->assertEquals('text', $log->content_type);
        $this->assertEquals(1.5, $log->credits_used);
    }

    public function test_log_usage_updates_module_statistics(): void
    {
        $company = Company::factory()->create();

        $this->service->logUsage(
            $company,
            null,
            'invoices',
            'text',
            'translation',
            'gpt-3.5-turbo',
            100,
            50,
            2.0,
            0.003
        );

        $stats = AIUsageByModule::where('company_id', $company->id)
            ->where('module_name', 'invoices')
            ->first();

        $this->assertNotNull($stats);
        $this->assertEquals(1, $stats->usage_count);
        $this->assertEquals(2.0, $stats->credits_used);
    }

    public function test_log_usage_updates_content_type_statistics(): void
    {
        $company = Company::factory()->create();

        $this->service->logUsage(
            $company,
            null,
            'invoices',
            'text',
            'translation',
            'gpt-3.5-turbo',
            100,
            50,
            1.5,
            0.002
        );

        $stats = AIUsageByContentType::where('company_id', $company->id)
            ->where('content_type', 'text')
            ->first();

        $this->assertNotNull($stats);
        $this->assertEquals(1, $stats->usage_count);
        $this->assertEquals(1.5, $stats->credits_used);
    }

    public function test_log_usage_updates_daily_summary(): void
    {
        $company = Company::factory()->create();

        $this->service->logUsage(
            $company,
            null,
            'invoices',
            'text',
            'translation',
            'gpt-3.5-turbo',
            100,
            50,
            2.0,
            0.003
        );

        $summary = AIUsageSummary::where('company_id', $company->id)
            ->whereDate('date', today())
            ->first();

        $this->assertNotNull($summary);
        $this->assertEquals(1, $summary->total_usage_count);
        $this->assertEquals(2.0, $summary->total_credits_used);
    }

    /**
     * Test getUsageByModule
     */
    public function test_get_usage_by_module_returns_correct_data(): void
    {
        $company = Company::factory()->create();

        $this->service->logUsage($company, null, 'invoices', 'text', 'translation', 'gpt-3.5-turbo', 100, 50, 2.0, 0.003);
        $this->service->logUsage($company, null, 'projects', 'text', 'translation', 'gpt-3.5-turbo', 100, 50, 1.5, 0.002);

        $usage = $this->service->getUsageByModule($company);

        $this->assertCount(2, $usage);
        $this->assertEquals('projects', $usage->first()->module_name); // Most recent first
    }

    /**
     * Test getUsageMatrix
     */
    public function test_get_usage_matrix_returns_correct_structure(): void
    {
        $company = Company::factory()->create();

        $this->service->logUsage($company, null, 'invoices', 'text', 'translation', 'gpt-3.5-turbo', 100, 50, 2.0, 0.003);
        $this->service->logUsage($company, null, 'invoices', 'image', 'generation', 'dall-e', 0, 0, 5.0, 0.01);

        $matrix = $this->service->getUsageMatrix($company);

        $this->assertArrayHasKey('invoices', $matrix);
        $this->assertArrayHasKey('text', $matrix['invoices']);
        $this->assertArrayHasKey('image', $matrix['invoices']);
        $this->assertEquals(2.0, $matrix['invoices']['text']['total_credits']);
        $this->assertEquals(5.0, $matrix['invoices']['image']['total_credits']);
    }
}

