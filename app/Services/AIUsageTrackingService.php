<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Models\AIUsageLog;
use App\Models\AIUsageByModule;
use App\Models\AIUsageByContentType;
use App\Models\AIUsageSummary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AIUsageTrackingService
{
    /**
     * Log AI usage
     */
    public function logUsage(
        Company $company,
        ?User $user,
        string $moduleName,
        string $contentType,
        string $serviceType,
        string $modelUsed,
        int $tokensInput,
        int $tokensOutput,
        float $creditsUsed,
        float $costUsd,
        array $requestData = [],
        array $responseData = [],
        int $durationMs = 0,
        string $status = 'success',
        ?string $errorMessage = null,
        ?string $featureUsed = null
    ): ?AIUsageLog {
        try {
            $log = AIUsageLog::create([
                'company_id' => $company->id,
                'user_id' => $user?->id,
                'module_name' => $moduleName,
                'content_type' => $contentType,
                'service_type' => $serviceType,
                'model_used' => $modelUsed,
                'tokens_input' => $tokensInput,
                'tokens_output' => $tokensOutput,
                'credits_used' => $creditsUsed,
                'cost_usd' => $costUsd,
                'request_data' => $requestData,
                'response_data' => $responseData,
                'duration_ms' => $durationMs,
                'status' => $status,
                'error_message' => $errorMessage,
                'feature_used' => $featureUsed,
            ]);

            // Update aggregated statistics
            $this->updateUsageStatistics($company, $moduleName, $contentType, $creditsUsed, $costUsd);

            return $log;
        } catch (\Exception $e) {
            Log::error('AI Usage Logging Error: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'module_name' => $moduleName,
            ]);

            return null;
        }
    }

    /**
     * Update aggregated usage statistics
     */
    protected function updateUsageStatistics(Company $company, string $moduleName, string $contentType, float $creditsUsed, float $costUsd): void
    {
        $today = now()->startOfDay();

        // Update module-based statistics
        AIUsageByModule::updateOrCreate(
            [
                'company_id' => $company->id,
                'module_name' => $moduleName,
                'date' => $today,
            ],
            [
                'usage_count' => DB::raw('usage_count + 1'),
                'credits_used' => DB::raw('credits_used + ' . $creditsUsed),
                'cost_usd' => DB::raw('cost_usd + ' . $costUsd),
            ]
        );

        // Update content type-based statistics
        AIUsageByContentType::updateOrCreate(
            [
                'company_id' => $company->id,
                'content_type' => $contentType,
                'date' => $today,
            ],
            [
                'usage_count' => DB::raw('usage_count + 1'),
                'credits_used' => DB::raw('credits_used + ' . $creditsUsed),
                'cost_usd' => DB::raw('cost_usd + ' . $costUsd),
            ]
        );

        // Update daily summary
        AIUsageSummary::updateOrCreate(
            [
                'company_id' => $company->id,
                'date' => $today,
            ],
            [
                'total_usage_count' => DB::raw('total_usage_count + 1'),
                'total_credits_used' => DB::raw('total_credits_used + ' . $creditsUsed),
                'total_cost_usd' => DB::raw('total_cost_usd + ' . $costUsd),
            ]
        );
    }

    /**
     * Get usage by module for a company
     */
    public function getUsageByModule(Company $company, ?Carbon $startDate = null, ?Carbon $endDate = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = AIUsageByModule::where('company_id', $company->id);

        if ($startDate) {
            $query->where('date', '>=', $startDate->startOfDay());
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate->endOfDay());
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('credits_used', 'desc')
            ->get();
    }

    /**
     * Get usage by content type for a company
     */
    public function getUsageByContentType(Company $company, ?Carbon $startDate = null, ?Carbon $endDate = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = AIUsageByContentType::where('company_id', $company->id);

        if ($startDate) {
            $query->where('date', '>=', $startDate->startOfDay());
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate->endOfDay());
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('credits_used', 'desc')
            ->get();
    }

    /**
     * Get usage summary for a company
     */
    public function getUsageSummary(Company $company, ?Carbon $startDate = null, ?Carbon $endDate = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = AIUsageSummary::where('company_id', $company->id);

        if ($startDate) {
            $query->where('date', '>=', $startDate->startOfDay());
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate->endOfDay());
        }

        return $query->orderBy('date', 'desc')->get();
    }

    /**
     * Get detailed usage logs
     */
    public function getUsageLogs(Company $company, ?Carbon $startDate = null, ?Carbon $endDate = null, int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        $query = AIUsageLog::where('company_id', $company->id)
            ->with('user');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate->startOfDay());
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate->endOfDay());
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get usage matrix (module x content type)
     */
    public function getUsageMatrix(Company $company, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = AIUsageLog::where('company_id', $company->id)
            ->select(
                'module_name',
                'content_type',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(credits_used) as total_credits'),
                DB::raw('SUM(cost_usd) as total_cost')
            )
            ->groupBy('module_name', 'content_type');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate->startOfDay());
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate->endOfDay());
        }

        $results = $query->get();

        $matrix = [];
        foreach ($results as $result) {
            if (!isset($matrix[$result->module_name])) {
                $matrix[$result->module_name] = [];
            }

            $matrix[$result->module_name][$result->content_type] = [
                'usage_count' => $result->usage_count,
                'total_credits' => (float) $result->total_credits,
                'total_cost' => (float) $result->total_cost,
            ];
        }

        return $matrix;
    }
}

