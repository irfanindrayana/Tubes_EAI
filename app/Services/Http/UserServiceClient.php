<?php

namespace App\Services\Http;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class UserServiceClient
{
    private string $baseUrl;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $this->baseUrl = env('USER_SERVICE_URL', 'http://user-service');
        $this->timeout = env('SERVICE_TIMEOUT', 30);
        $this->retries = env('MAX_RETRIES', 3);
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $userId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/users/{$userId}");
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("UserServiceClient: Failed to get user {$userId}", [
                'error' => $e->getMessage(),
                'service' => 'user-service'
            ]);
            return null;
        }
    }

    /**
     * Get multiple users by IDs
     */
    public function getUsersByIds(array $userIds): array
    {
        try {
            $response = $this->makeRequest('POST', '/api/v1/internal/users/multiple', [
                'user_ids' => $userIds
            ]);
            return $response['data'] ?? [];
        } catch (Exception $e) {
            Log::error("UserServiceClient: Failed to get users", [
                'user_ids' => $userIds,
                'error' => $e->getMessage(),
                'service' => 'user-service'
            ]);
            return [];
        }
    }

    /**
     * Check if user exists
     */
    public function userExists(int $userId): bool
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/users/{$userId}/exists");
            return $response['exists'] ?? false;
        } catch (Exception $e) {
            Log::error("UserServiceClient: Failed to check user existence {$userId}", [
                'error' => $e->getMessage(),
                'service' => 'user-service'
            ]);
            return false;
        }
    }

    /**
     * Get user basic info
     */
    public function getUserBasicInfo(int $userId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/users/{$userId}/basic-info");
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("UserServiceClient: Failed to get user basic info {$userId}", [
                'error' => $e->getMessage(),
                'service' => 'user-service'
            ]);
            return null;
        }
    }

    /**
     * Get user profile
     */
    public function getUserProfile(int $userId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/users/{$userId}/profile");
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("UserServiceClient: Failed to get user profile {$userId}", [
                'error' => $e->getMessage(),
                'service' => 'user-service'
            ]);
            return null;
        }
    }

    /**
     * Make HTTP request with retry logic
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retries) {
            try {
                $url = $this->baseUrl . $endpoint;
                
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'X-Service-Source' => env('SERVICE_NAME', 'unknown'),
                        'X-Request-ID' => uniqid('req_', true)
                    ])
                    ->when($method === 'GET', function ($http) use ($data) {
                        return $http->get($data);
                    })
                    ->when($method === 'POST', function ($http) use ($url, $data) {
                        return $http->post($url, $data);
                    })
                    ->when($method === 'PUT', function ($http) use ($url, $data) {
                        return $http->put($url, $data);
                    })
                    ->when($method === 'DELETE', function ($http) use ($url) {
                        return $http->delete($url);
                    });

                if ($response->successful()) {
                    return $response->json();
                }

                throw new Exception("HTTP {$response->status()}: " . $response->body());
                
            } catch (Exception $e) {
                $attempt++;
                $lastException = $e;
                
                if ($attempt < $this->retries) {
                    // Exponential backoff: 1s, 2s, 4s
                    sleep(pow(2, $attempt - 1));
                }
            }
        }

        throw $lastException;
    }

    /**
     * Health check
     */
    public function healthCheck(): array
    {
        try {
            $response = $this->makeRequest('GET', '/health');
            return [
                'service' => 'user-service',
                'status' => 'healthy',
                'response_time' => $response['timestamp'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'service' => 'user-service',
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }
}
