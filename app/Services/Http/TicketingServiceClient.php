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
     * Get available routes
     */
    public function getRoutes(): ?array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v1/internal/routes');
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to get routes", [
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return null;
        }
    }

    /**
     * Get route by ID
     */
    public function getRouteById(int $routeId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/routes/{$routeId}");
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
     * Get schedules for a route
     */
    public function getSchedulesByRoute(int $routeId, ?string $date = null): ?array
    {
        try {
            $params = $date ? ['date' => $date] : [];
            $response = $this->makeRequest('GET', "/api/v1/internal/routes/{$routeId}/schedules", $params);
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to get schedules for route {$routeId}", [
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return null;
        }
    }

    /**
     * Get available seats for a schedule
     */
    public function getAvailableSeats(int $scheduleId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/schedules/{$scheduleId}/seats");
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to get seats for schedule {$scheduleId}", [
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
            ]);
            return null;
        }
    }

    /**
     * Create a booking
     */
    public function createBooking(array $bookingData): ?array
    {
        try {
            $response = $this->makeRequest('POST', '/api/v1/internal/bookings', $bookingData);
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to create booking", [
                'error' => $e->getMessage(),
                'booking_data' => $bookingData,
                'service' => 'ticketing-service'
            ]);
            return null;
        }
    }

    /**
     * Get booking by ID
     */
    public function getBookingById(int $bookingId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/bookings/{$bookingId}");
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
            $this->makeRequest('PUT', "/api/v1/internal/bookings/{$bookingId}/status", ['status' => $status]);
            return true;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to update booking {$bookingId} status", [
                'error' => $e->getMessage(),
                'status' => $status,
                'service' => 'ticketing-service'
            ]);
            return false;
        }
    }

    /**
     * Get user bookings
     */
    public function getUserBookings(int $userId): ?array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/internal/users/{$userId}/bookings");
            return $response['data'] ?? null;
        } catch (Exception $e) {
            Log::error("TicketingServiceClient: Failed to get bookings for user {$userId}", [
                'error' => $e->getMessage(),
                'service' => 'ticketing-service'
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
                        'Content-Type' => 'application/json',
                    ]);

                switch (strtoupper($method)) {
                    case 'GET':
                        $response = $response->get($url, $data);
                        break;
                    case 'POST':
                        $response = $response->post($url, $data);
                        break;
                    case 'PUT':
                        $response = $response->put($url, $data);
                        break;
                    case 'DELETE':
                        $response = $response->delete($url, $data);
                        break;
                    default:
                        throw new Exception("Unsupported HTTP method: {$method}");
                }

                if ($response->successful()) {
                    return $response->json();
                }

                throw new Exception("HTTP {$response->status()}: " . $response->body());

            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt < $this->retries) {
                    // Exponential backoff
                    sleep(pow(2, $attempt - 1));
                }
            }
        }

        throw $lastException;
    }
}
