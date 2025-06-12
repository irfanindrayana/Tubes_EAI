<?php

namespace App\Services\Http;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class InboxServiceClient
{
    private string $baseUrl;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $this->baseUrl = env('INBOX_SERVICE_URL', 'http://inbox-service');
        $this->timeout = env('SERVICE_TIMEOUT', 30);
        $this->retries = env('MAX_RETRIES', 3);
    }

    /**
     * Send message
     */
    public function sendMessage(array $messageData): array
    {
        try {
            $response = $this->makeRequest('POST', '/api/v1/internal/inbox/messages', $messageData);
            return $response;
        } catch (Exception $e) {
            Log::error("InboxServiceClient: Failed to send message", [
                'message_data' => $messageData,
                'error' => $e->getMessage(),
                'service' => 'inbox-service'
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send notification
     */
    public function sendNotification(array $notificationData): array
    {
        try {
            $response = $this->makeRequest('POST', '/api/v1/internal/inbox/notifications', $notificationData);
            return $response;
        } catch (Exception $e) {
            Log::error("InboxServiceClient: Failed to send notification", [
                'notification_data' => $notificationData,
                'error' => $e->getMessage(),
                'service' => 'inbox-service'
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get user messages
     */
    public function getUserMessages(int $userId, int $perPage = 15): array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/inbox/user/{$userId}/messages", [
                'per_page' => $perPage
            ]);
            return $response['data'] ?? [];
        } catch (Exception $e) {
            Log::error("InboxServiceClient: Failed to get user messages {$userId}", [
                'error' => $e->getMessage(),
                'service' => 'inbox-service'
            ]);
            return [];
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId): array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/inbox/user/{$userId}/notifications");
            return $response['data'] ?? [];
        } catch (Exception $e) {
            Log::error("InboxServiceClient: Failed to get user notifications {$userId}", [
                'error' => $e->getMessage(),
                'service' => 'inbox-service'
            ]);
            return [];
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(int $messageId, int $userId): bool
    {
        try {
            $response = $this->makeRequest('PUT', "/api/v1/internal/inbox/messages/{$messageId}/read", [
                'user_id' => $userId
            ]);
            return $response['success'] ?? false;
        } catch (Exception $e) {
            Log::error("InboxServiceClient: Failed to mark message as read {$messageId}", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'service' => 'inbox-service'
            ]);
            return false;
        }
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(int $userId): int
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/inbox/user/{$userId}/unread-count");
            return $response['count'] ?? 0;
        } catch (Exception $e) {
            Log::error("InboxServiceClient: Failed to get unread count {$userId}", [
                'error' => $e->getMessage(),
                'service' => 'inbox-service'
            ]);
            return 0;
        }
    }

    /**
     * Send bulk messages
     */
    public function sendBulkMessages(array $messages): array
    {
        try {
            $response = $this->makeRequest('POST', '/api/v1/internal/inbox/messages/bulk', [
                'messages' => $messages
            ]);
            return $response;
        } catch (Exception $e) {
            Log::error("InboxServiceClient: Failed to send bulk messages", [
                'message_count' => count($messages),
                'error' => $e->getMessage(),
                'service' => 'inbox-service'
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send bulk notifications
     */
    public function sendBulkNotifications(array $notifications): array
    {
        try {
            $response = $this->makeRequest('POST', '/api/v1/internal/inbox/notifications/bulk', [
                'notifications' => $notifications
            ]);
            return $response;
        } catch (Exception $e) {
            Log::error("InboxServiceClient: Failed to send bulk notifications", [
                'notification_count' => count($notifications),
                'error' => $e->getMessage(),
                'service' => 'inbox-service'
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
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
                    ->when($method === 'GET', function ($http) use ($url, $data) {
                        return empty($data) ? $http->get($url) : $http->get($url, $data);
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
                'service' => 'inbox-service',
                'status' => 'healthy',
                'response_time' => $response['timestamp'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'service' => 'inbox-service',
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }
}
