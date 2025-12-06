<?php

namespace App\Http\Controllers\SuperAdmin\FrontSetting;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\GlobalSetting;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AiSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'AI Settings';
        $this->activeSettingMenu = 'ai_settings';
        $this->activeMenu = 'ai_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_superadmin_front_settings')));
            return $next($request);
        });

        $this->aiApiKey = $this->readEnvValue('OPENROUTER_API_KEY') ?? '';
        $this->currentLlm = $this->readEnvValue('AI_LLM_MODEL') ?? 'openai/gpt-3.5-turbo';
        $this->tokenUsage = $this->getTokenUsage();
    }

    /**
     * Display AI Settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->pageTitle = 'superadmin.menu.aiSettings';
        $this->activeSettingMenu = 'ai_settings';
        $this->view = 'super-admin.front-setting.ai-settings.index';

        $this->aiApiKey = $this->readEnvValue('OPENROUTER_API_KEY') ?? '';
        $this->currentLlm = $this->readEnvValue('AI_LLM_MODEL') ?? 'openai/gpt-3.5-turbo';
        
        // Get available models from OpenRouter
        $this->availableModels = $this->getAvailableModels();

        // Get token usage data and convert from JsonResponse to array
        $tokenUsageResponse = $this->getTokenUsage();
        $tokenUsageData = json_decode($tokenUsageResponse->getContent(), true);

        if ($tokenUsageData['success']) {
            $this->tokenUsage = [
                'total_tokens_used' => $tokenUsageData['data']['total_tokens'],
                'tokens_remaining' => max(0, 1000000 - $tokenUsageData['data']['total_tokens']),
                'monthly_limit' => 1000000,
                'current_month_usage' => $tokenUsageData['data']['total_tokens'],
                'last_updated' => $tokenUsageData['data']['last_updated']
            ];
        } else {
            // Fallback data if API call fails
            $this->tokenUsage = [
                'total_tokens_used' => 0,
                'tokens_remaining' => 1000000,
                'monthly_limit' => 1000000,
                'current_month_usage' => 0,
                'last_updated' => now()->format('Y-m-d H:i:s')
            ];
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('super-admin.front-setting.ai-settings.index', $this->data);
    }

    /**
     * Update OpenRouter API Key
     */
    public function updateApiKey(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string|min:10'
        ]);

        try {
            // Update the API key in environment file
            $this->updateEnvFile('OPENROUTER_API_KEY', $request->api_key);

            // Clear config cache
            \Artisan::call('config:clear');

            return response()->json([
                'status' => 'success',
                'message' => __('superadmin.aiSettings.apiKeyUpdated')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update API key: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Switch LLM model (OpenRouter model selection)
     */
    public function switchLlm(Request $request)
    {
        $request->validate([
            'llm_model' => 'required|string'
        ]);

        try {
            // Update the LLM model in environment file
            $this->updateEnvFile('AI_LLM_MODEL', $request->llm_model);

            // Clear config cache
            \Artisan::call('config:clear');

            return response()->json([
                'status' => 'success',
                'message' => __('superadmin.aiSettings.llmSwitched')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to switch LLM model: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available models from OpenRouter
     */
    public function getAvailableModels()
    {
        try {
            $apiKey = env('OPENROUTER_API_KEY');
            
            if (!$apiKey) {
                return $this->getDefaultModels();
            }

            $client = new \GuzzleHttp\Client();
            
            $response = $client->get('https://openrouter.ai/api/v1/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                
                if (isset($data['data']) && is_array($data['data'])) {
                    $models = [];
                    foreach ($data['data'] as $model) {
                        if (isset($model['id'])) {
                            $models[] = [
                                'id' => $model['id'],
                                'name' => $model['name'] ?? $model['id'],
                                'context_length' => $model['context_length'] ?? null,
                                'pricing' => $model['pricing'] ?? null,
                            ];
                        }
                    }
                    return $models;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch OpenRouter models: ' . $e->getMessage());
        }
        
        // Fallback to default models if API call fails
        return $this->getDefaultModels();
    }
    
    /**
     * Get default list of popular OpenRouter models
     */
    private function getDefaultModels()
    {
        return [
            ['id' => 'openai/gpt-4-turbo', 'name' => 'GPT-4 Turbo (OpenAI)'],
            ['id' => 'openai/gpt-4', 'name' => 'GPT-4 (OpenAI)'],
            ['id' => 'openai/gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo (OpenAI)'],
            ['id' => 'anthropic/claude-3-opus', 'name' => 'Claude 3 Opus (Anthropic)'],
            ['id' => 'anthropic/claude-3-sonnet', 'name' => 'Claude 3 Sonnet (Anthropic)'],
            ['id' => 'anthropic/claude-3-haiku', 'name' => 'Claude 3 Haiku (Anthropic)'],
            ['id' => 'google/gemini-pro', 'name' => 'Gemini Pro (Google)'],
            ['id' => 'meta-llama/llama-3-70b-instruct', 'name' => 'Llama 3 70B (Meta)'],
            ['id' => 'meta-llama/llama-3-8b-instruct', 'name' => 'Llama 3 8B (Meta)'],
        ];
    }

    /**
     * Get token usage statistics from OpenRouter
     */
    public function getTokenUsage()
    {
        $key = 'ai_usage:' . now()->format('Y-m');
        $usage = \Cache::get($key);

        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        if (!$usage) {
            return response()->json([
                'success' => false,
                'message' => 'No AI usage captured yet',
            ]);
        }

        $totalTokens = (int) ($usage['total_tokens'] ?? 0);
        $promptTokens = (int) ($usage['prompt_tokens'] ?? 0);
        $completionTokens = (int) ($usage['completion_tokens'] ?? 0);
        $requestsCount = (int) ($usage['requests_count'] ?? 0);

        $estimatedCost = $this->calculateCost($totalTokens, $this->currentLlm);

        return response()->json([
            'success' => true,
            'data' => [
                'total_tokens' => $totalTokens,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_cost' => $estimatedCost,
                'requests_count' => $requestsCount,
                'by_model' => $usage['by_model'] ?? [],
                'by_feature' => $usage['by_feature'] ?? [],
                'events' => array_slice($usage['events'] ?? [], -20),
                'last_updated' => $usage['last_updated'] ?? now()->format('Y-m-d H:i:s'),
                'billing_period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
            ]
        ]);
    }

    /**
     * Calculate estimated cost based on token usage
     */
    private function calculateCost($totalTokens, $model = 'gpt-3.5-turbo')
    {
        $pricingPer1K = [
            'openai/gpt-3.5-turbo' => 0.002,
            'openai/gpt-4' => 0.06,
            'openai/gpt-4-turbo' => 0.03,
            'anthropic/claude-3-sonnet' => 0.016,
            'anthropic/claude-3-opus' => 0.075,
            'anthropic/claude-3-haiku' => 0.00125,
            'google/gemini-pro' => 0.0005,
            'meta-llama/llama-3-70b-instruct' => 0.005,
            'meta-llama/llama-3-8b-instruct' => 0.0002,
        ];

        $mappedModel = $model;
        if (!isset($pricingPer1K[$mappedModel])) {
            if (strpos($model, 'gpt-3.5') !== false) $mappedModel = 'openai/gpt-3.5-turbo';
            elseif (strpos($model, 'gpt-4-turbo') !== false) $mappedModel = 'openai/gpt-4-turbo';
            elseif (strpos($model, 'gpt-4') !== false) $mappedModel = 'openai/gpt-4';
            elseif (strpos($model, 'claude-3-opus') !== false) $mappedModel = 'anthropic/claude-3-opus';
            elseif (strpos($model, 'claude-3-sonnet') !== false) $mappedModel = 'anthropic/claude-3-sonnet';
            elseif (strpos($model, 'claude-3-haiku') !== false) $mappedModel = 'anthropic/claude-3-haiku';
            elseif (strpos($model, 'gemini') !== false) $mappedModel = 'google/gemini-pro';
            elseif (strpos($model, 'llama-3-70b') !== false) $mappedModel = 'meta-llama/llama-3-70b-instruct';
            elseif (strpos($model, 'llama-3-8b') !== false) $mappedModel = 'meta-llama/llama-3-8b-instruct';
            else $mappedModel = 'openai/gpt-3.5-turbo';
        }

        $pricePerToken = ($pricingPer1K[$mappedModel] ?? 0.002) / 1000;
        return round($totalTokens * $pricePerToken, 4);
    }

    /**
     * Refresh token usage data
     */
    public function refreshTokenUsage()
    {
        try {
            // Refresh token usage from API
            $tokenUsageResponse = $this->getTokenUsage();
            $tokenUsageData = json_decode($tokenUsageResponse->getContent(), true);

            if ($tokenUsageData['success']) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'total_tokens_used' => $tokenUsageData['data']['total_tokens'],
                        'tokens_remaining' => max(0, 1000000 - $tokenUsageData['data']['total_tokens']), // 假设100万token限制
                        'monthly_limit' => 1000000,
                        'current_month_usage' => $tokenUsageData['data']['total_tokens'],
                        'last_updated' => $tokenUsageData['data']['last_updated']
                        ,'by_model' => $tokenUsageData['data']['by_model'] ?? []
                        ,'by_feature' => $tokenUsageData['data']['by_feature'] ?? []
                        ,'events' => $tokenUsageData['data']['events'] ?? []
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $tokenUsageData['message'] ?? 'Failed to get token usage'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh token usage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test OpenRouter connection
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string'
        ]);

        try {
            $apiKey = $request->api_key ?: env('OPENROUTER_API_KEY');

            // Test the connection to OpenRouter
            $success = $this->testOpenRouterConnection($apiKey);

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('superadmin.aiSettings.connectionSuccess')
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => __('superadmin.aiSettings.connectionFailed')
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('superadmin.aiSettings.connectionFailed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test OpenRouter API connection
     */
    private function testOpenRouterConnection($apiKey)
    {
        $client = new \GuzzleHttp\Client();

        try {
            // Test by fetching models list
            $response = $client->get('https://openrouter.ai/api/v1/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 10
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            \Log::error('OpenRouter Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update environment file
     */
    private function updateEnvFile($key, $value)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        // Check if key exists
        if (strpos($envContent, $key . '=') !== false) {
            // Update existing key
            $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
        } else {
            // Add new key
            $envContent .= "\n{$key}={$value}";
        }

        file_put_contents($envFile, $envContent);
        \Artisan::call('config:clear');
    }

    private function readEnvValue($key)
    {
        $envFile = base_path('.env');
        if (!is_file($envFile)) {
            return null;
        }
        $content = file_get_contents($envFile);
        if ($content === false) {
            return null;
        }
        if (preg_match("/^" . preg_quote($key, '/') . "=(.*)$/m", $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
