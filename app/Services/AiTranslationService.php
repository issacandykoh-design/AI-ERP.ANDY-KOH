<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AiTranslationService
{
    private $apiKey;
    private $model;
    private $client;

    public function __construct()
    {
        $this->apiKey = env('OPENROUTER_API_KEY');
        $this->model = env('AI_LLM_MODEL', 'openai/gpt-3.5-turbo');
        $this->client = new Client([
            'base_uri' => 'https://openrouter.ai/api/v1/',
            'timeout' => 60,
        ]);
    }

    /**
     * Translate a single text string
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): ?string
    {
        if (empty($text) || empty($this->apiKey)) {
            return null;
        }

        try {
            $response = $this->client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name'),
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->getSystemPrompt($targetLanguage, $sourceLanguage),
                        ],
                        [
                            'role' => 'user',
                            'content' => $text,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 1000,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            $translated = isset($data['choices'][0]['message']['content']) ? trim($data['choices'][0]['message']['content']) : null;

            $usage = [
                'prompt_tokens' => $data['usage']['prompt_tokens'] ?? null,
                'completion_tokens' => $data['usage']['completion_tokens'] ?? null,
                'total_tokens' => $data['usage']['total_tokens'] ?? null,
            ];

            if ($translated !== null) {
                if ($usage['total_tokens'] === null) {
                    $usage['prompt_tokens'] = $usage['prompt_tokens'] ?? max(1, (int) floor(strlen($text) / 4));
                    $usage['completion_tokens'] = $usage['completion_tokens'] ?? max(1, (int) floor(strlen($translated) / 4));
                    $usage['total_tokens'] = $usage['prompt_tokens'] + $usage['completion_tokens'];
                }
                $this->recordUsage($this->model, $usage, 'translation');
                return $translated;
            }

            return null;
        } catch (GuzzleException $e) {
            Log::error('AI Translation Error: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('AI Translation Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Translate multiple texts in a single batch
     */
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        if (empty($texts) || empty($this->apiKey)) {
            return [];
        }

        $results = [];
        $batchSize = 20; // Process in batches to avoid token limits

        foreach (array_chunk($texts, $batchSize, true) as $batch) {
            $batchResults = $this->translateBatchChunk($batch, $targetLanguage, $sourceLanguage);
            $results = array_merge($results, $batchResults);
        }

        return $results;
    }

    /**
     * Translate a chunk of texts
     */
    private function translateBatchChunk(array $texts, string $targetLanguage, string $sourceLanguage): array
    {
        // For better reliability, translate individually in small batches
        // Batch API calls can be unreliable with complex translations
        return $this->translateBatchFallback($texts, $targetLanguage, $sourceLanguage);
    }

    /**
     * Fallback: translate individually if batch fails
     */
    private function translateBatchFallback(array $texts, string $targetLanguage, string $sourceLanguage): array
    {
        $results = [];
        foreach ($texts as $key => $text) {
            $results[$key] = $this->translate($text, $targetLanguage, $sourceLanguage) ?: $text;
        }
        return $results;
    }

    /**
     * Parse batch response into key-value array
     */
    private function parseBatchResponse(string $response, array $keys): array
    {
        $results = [];
        $lines = explode("\n", $response);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Try to match "key: translation" format
            if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                $key = trim($matches[1]);
                $translation = trim($matches[2]);
                if (in_array($key, $keys)) {
                    $results[$key] = $translation;
                }
            }
        }

        // Fill missing keys with original text
        foreach ($keys as $key) {
            if (!isset($results[$key])) {
                $results[$key] = null;
            }
        }

        return $results;
    }

    /**
     * Get system prompt for single translation
     */
    private function getSystemPrompt(string $targetLanguage, string $sourceLanguage): string
    {
        $targetLangName = $this->getLanguageName($targetLanguage);
        $sourceLangName = $this->getLanguageName($sourceLanguage);

        return "You are a professional translator. Translate the following text from {$sourceLangName} ({$sourceLanguage}) to {$targetLangName} ({$targetLanguage}). 
        
Rules:
- Translate accurately and naturally
- Preserve any HTML tags, placeholders like :variable, and special formatting
- Maintain the same tone and style
- Return ONLY the translated text, nothing else
- Do not add explanations or notes";
    }

    /**
     * Get system prompt for batch translation
     */
    private function getBatchSystemPrompt(string $targetLanguage, string $sourceLanguage): string
    {
        $targetLangName = $this->getLanguageName($targetLanguage);
        $sourceLangName = $this->getLanguageName($sourceLanguage);

        return "You are a professional translator. Translate the following key-value pairs from {$sourceLangName} ({$sourceLanguage}) to {$targetLangName} ({$targetLanguage}).

Input format: key: text
Output format: key: translated_text (one per line)

Rules:
- Translate accurately and naturally
- Preserve any HTML tags, placeholders like :variable, and special formatting
- Maintain the same tone and style
- Keep the exact key names unchanged
- Return ONLY the key: translation pairs, one per line
- Do not add explanations or notes";
    }

    /**
     * Get language name from code
     */
    private function getLanguageName(string $code): string
    {
        $languages = [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'ar' => 'Arabic',
            'hi' => 'Hindi',
            'th' => 'Thai',
            'vi' => 'Vietnamese',
            'id' => 'Indonesian',
            'ms' => 'Malay',
            'tr' => 'Turkish',
            'pl' => 'Polish',
            'nl' => 'Dutch',
            'sv' => 'Swedish',
            'da' => 'Danish',
            'no' => 'Norwegian',
            'fi' => 'Finnish',
        ];

        return $languages[$code] ?? ucfirst($code);
    }

    /**
     * Check if API is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $testTranslation = $this->translate('Hello', 'es', 'en');
            return !empty($testTranslation);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function recordUsage(string $model, array $usage, string $feature): void
    {
        try {
            $key = 'ai_usage:' . now()->format('Y-m');
            $data = Cache::get($key, [
                'total_tokens' => 0,
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'requests_count' => 0,
                'by_model' => [],
                'by_feature' => [],
                'events' => [],
                'last_updated' => now()->format('Y-m-d H:i:s'),
            ]);

            $pt = (int) ($usage['prompt_tokens'] ?? 0);
            $ct = (int) ($usage['completion_tokens'] ?? 0);
            $tt = (int) ($usage['total_tokens'] ?? ($pt + $ct));

            $data['prompt_tokens'] += $pt;
            $data['completion_tokens'] += $ct;
            $data['total_tokens'] += $tt;
            $data['requests_count'] += 1;

            $data['by_model'][$model] = ($data['by_model'][$model] ?? 0) + $tt;
            $data['by_feature'][$feature] = ($data['by_feature'][$feature] ?? 0) + $tt;

            $data['events'][] = [
                'ts' => now()->format('Y-m-d H:i:s'),
                'model' => $model,
                'feature' => $feature,
                'prompt_tokens' => $pt,
                'completion_tokens' => $ct,
                'total_tokens' => $tt,
            ];
            if (count($data['events']) > 100) {
                $data['events'] = array_slice($data['events'], -100);
            }
            $data['last_updated'] = now()->format('Y-m-d H:i:s');

            Cache::put($key, $data, now()->addDays(31));
        } catch (\Throwable $e) {
            Log::info('AI usage record error: ' . $e->getMessage());
        }
    }

    public static function recordUsageEvent(string $model, array $usage, string $feature): void
    {
        try {
            $service = new self();
            $service->recordUsage($model, $usage, $feature);
        } catch (\Throwable $e) {
            Log::info('AI usage record (static) error: ' . $e->getMessage());
        }
    }
}

