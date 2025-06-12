<?php

namespace App\Services\Http;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TicketingServiceClient
{
    private string $baseUrl;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $this->baseUrl = env('TICKETING_SERVICE_URL', 'http://ticketing-service');
        $this->timeout = env('SERVICE_TIMEOUT', 30);
        $this->retries = env('MAX_RETRIES', 3);
    }

    /**
     * Get route information
     */
    public function getRoute(int $routeId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/ticketing/routes/{$routeId}");
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to get route {$routeId}", [
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return null;
        }
    }

    /**
     * Get schedule information
     */
    public function getSchedule(int $scheduleId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/ticketing/schedules/{$scheduleId}");
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to get schedule {$scheduleId}", [
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return null;
        }
    }

    /**
     * Check seat availability
     */
    public function checkSeatAvailability(int $scheduleId, string $date): array
    {
        try {
            $response = $this->makeRequest('POST', '/api/v1/internal/ticketing/seats/availability', [
                'schedule_id' => $scheduleId,
                'date' => $date
            ]);
            return $response['data'] ?? [];
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to check seat availability", [
                'schedule_id' => $scheduleId,
                'date' => $date,
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return ['available' => false, 'seats' => []];
        }
    }

    /**
     * Create booking
     */
    public function createBooking(array $bookingData): array
    {
        try {
            $response = $this->makeRequest('POST', '/api/v1/internal/ticketing/bookings', $bookingData);
            return $response;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to create booking", [
                'booking_data' => $bookingData,
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get booking information
     */
    public function getBooking(int $bookingId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/ticketing/bookings/{$bookingId}");
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to get booking {$bookingId}", [
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return null;
        }
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus(int $bookingId, string $status): bool
    {
        try {
            $response = $this->makeRequest('PUT', "/api/v1/internal/ticketing/bookings/{$bookingId}/status", [
                'status' => $status
            ]);
            return $response['success'] ?? false;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to update booking status {$bookingId}", [
                'status' => $status,
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return false;
        }
    }

    /**
     * Get user bookings
     */
    public function getUserBookings(int $userId): array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/ticketing/user/{$userId}/bookings");
            return $response['data'] ?? [];
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to get user bookings {$userId}", [
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return [];
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
                'service' => 'ticketing-service',
                'status' => 'healthy',
                'response_time' => $response['timestamp'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'service' => 'ticketing-service',
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }
}
