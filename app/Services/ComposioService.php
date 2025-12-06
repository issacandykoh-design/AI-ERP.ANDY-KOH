<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ComposioService
{
    private string $apiKey;

    private Client $client;

    private string $baseUri;

    public function __construct()
    {
        $this->apiKey = config('services.composio.api_key', '');
        $this->baseUri = config('services.composio.base_uri', 'https://api.composio.dev/v1');
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 60,
            'headers' => [
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Check if Composio is configured
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * List all available tools/integrations
     */
    public function listTools(array $params = []): ?array
    {
        if (! $this->isConfigured()) {
            Log::warning('Composio API key not configured');

            return null;
        }

        try {
            $response = $this->client->get('tools', [
                'query' => $params,
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['data'] ?? $data ?? null;
        } catch (GuzzleException $e) {
            Log::error('Composio List Tools Error: '.$e->getMessage());

            return null;
        } catch (\Exception $e) {
            Log::error('Composio List Tools Error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get details of a specific tool
     */
    public function getTool(string $toolName): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client->get("tools/{$toolName}");

            $data = json_decode($response->getBody(), true);

            return $data['data'] ?? $data ?? null;
        } catch (GuzzleException $e) {
            Log::error("Composio Get Tool Error ({$toolName}): ".$e->getMessage());

            return null;
        } catch (\Exception $e) {
            Log::error("Composio Get Tool Error ({$toolName}): ".$e->getMessage());

            return null;
        }
    }

    /**
     * Execute a tool action
     */
    public function executeAction(
        string $toolName,
        string $actionName,
        array $parameters = [],
        ?string $entityId = null
    ): ?array {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $payload = [
                'name' => $actionName,
                'parameters' => $parameters,
            ];

            if ($entityId) {
                $payload['entityId'] = $entityId;
            }

            $response = $this->client->post("tools/{$toolName}/actions/execute", [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['data'] ?? $data ?? null;
        } catch (GuzzleException $e) {
            Log::error("Composio Execute Action Error ({$toolName}/{$actionName}): ".$e->getMessage());

            return null;
        } catch (\Exception $e) {
            Log::error("Composio Execute Action Error ({$toolName}/{$actionName}): ".$e->getMessage());

            return null;
        }
    }

    /**
     * Get available actions for a tool
     */
    public function getToolActions(string $toolName): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client->get("tools/{$toolName}/actions");

            $data = json_decode($response->getBody(), true);

            return $data['data'] ?? $data ?? null;
        } catch (GuzzleException $e) {
            Log::error("Composio Get Tool Actions Error ({$toolName}): ".$e->getMessage());

            return null;
        } catch (\Exception $e) {
            Log::error("Composio Get Tool Actions Error ({$toolName}): ".$e->getMessage());

            return null;
        }
    }

    /**
     * Create or get a connection to a tool
     */
    public function createConnection(string $toolName, array $credentials = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client->post('connections', [
                'json' => [
                    'appName' => $toolName,
                    'entityId' => $credentials['entityId'] ?? null,
                    'credentials' => $credentials,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['data'] ?? $data ?? null;
        } catch (GuzzleException $e) {
            Log::error("Composio Create Connection Error ({$toolName}): ".$e->getMessage());

            return null;
        } catch (\Exception $e) {
            Log::error("Composio Create Connection Error ({$toolName}): ".$e->getMessage());

            return null;
        }
    }

    /**
     * Get all connections
     */
    public function listConnections(?string $entityId = null): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $query = [];
            if ($entityId) {
                $query['entityId'] = $entityId;
            }

            $response = $this->client->get('connections', [
                'query' => $query,
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['data'] ?? $data ?? null;
        } catch (GuzzleException $e) {
            Log::error('Composio List Connections Error: '.$e->getMessage());

            return null;
        } catch (\Exception $e) {
            Log::error('Composio List Connections Error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get a specific connection
     */
    public function getConnection(string $connectionId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client->get("connections/{$connectionId}");

            $data = json_decode($response->getBody(), true);

            return $data['data'] ?? $data ?? null;
        } catch (GuzzleException $e) {
            Log::error("Composio Get Connection Error ({$connectionId}): ".$e->getMessage());

            return null;
        } catch (\Exception $e) {
            Log::error("Composio Get Connection Error ({$connectionId}): ".$e->getMessage());

            return null;
        }
    }

    /**
     * Delete a connection
     */
    public function deleteConnection(string $connectionId): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $this->client->delete("connections/{$connectionId}");

            return true;
        } catch (GuzzleException $e) {
            Log::error("Composio Delete Connection Error ({$connectionId}): ".$e->getMessage());

            return false;
        } catch (\Exception $e) {
            Log::error("Composio Delete Connection Error ({$connectionId}): ".$e->getMessage());

            return false;
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $response = $this->listTools(['limit' => 1]);

            return $response !== null;
        } catch (\Exception $e) {
            Log::error('Composio Test Connection Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Search for tools by name or category
     */
    public function searchTools(string $query, array $filters = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $params = array_merge(['query' => $query], $filters);

            $response = $this->client->get('tools/search', [
                'query' => $params,
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['data'] ?? $data ?? null;
        } catch (GuzzleException $e) {
            Log::error('Composio Search Tools Error: '.$e->getMessage());

            return null;
        } catch (\Exception $e) {
            Log::error('Composio Search Tools Error: '.$e->getMessage());

            return null;
        }
    }
}
