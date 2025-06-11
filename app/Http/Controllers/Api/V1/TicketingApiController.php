<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Contracts\TicketingServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Ticketing API Controller
 * Handles all ticketing-related operations for microservice communication
 */
class TicketingApiController extends Controller
{
    protected TicketingServiceInterface $ticketingService;

    public function __construct(TicketingServiceInterface $ticketingService)
    {
        $this->ticketingService = $ticketingService;
    }

    /**
     * Get route information
     */
    public function getRoute(int $routeId): JsonResponse
    {
        $route = $this->ticketingService->getRoute($routeId);
        
        if (!$route) {
            return response()->json(['error' => 'Route not found'], 404);
        }
        
        return response()->json(['data' => $route]);
    }

    /**
     * Get schedule information
     */
    public function getSchedule(int $scheduleId): JsonResponse
    {
        $schedule = $this->ticketingService->getSchedule($scheduleId);
        
        if (!$schedule) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }
        
        return response()->json(['data' => $schedule]);
    }

    /**
     * Check seat availability
     */
    public function checkSeatAvailability(Request $request): JsonResponse
    {
        $scheduleId = $request->input('schedule_id');
        $date = $request->input('date');
        
        if (!$scheduleId || !$date) {
            return response()->json(['error' => 'Schedule ID and date required'], 400);
        }
        
        $availability = $this->ticketingService->checkSeatAvailability($scheduleId, $date);
        
        return response()->json(['data' => $availability]);
    }

    /**
     * Create booking
     */
    public function createBooking(Request $request): JsonResponse
    {
        $bookingData = $request->validate([
            'user_id' => 'required|integer',
            'schedule_id' => 'required|integer',
            'travel_date' => 'required|date',
            'seat_count' => 'required|integer|min:1',
            'seat_numbers' => 'required|array',
            'passenger_details' => 'required|array',
            'total_amount' => 'required|numeric|min:0',
        ]);
        
        $result = $this->ticketingService->createBooking($bookingData);
        
        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Get booking information
     */
    public function getBooking(int $bookingId): JsonResponse
    {
        $booking = $this->ticketingService->getBooking($bookingId);
        
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        
        return response()->json(['data' => $booking]);
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus(Request $request, int $bookingId): JsonResponse
    {
        $status = $request->input('status');
        
        if (!$status) {
            return response()->json(['error' => 'Status required'], 400);
        }
        
        $updated = $this->ticketingService->updateBookingStatus($bookingId, $status);
        
        if (!$updated) {
            return response()->json(['error' => 'Booking not found or update failed'], 404);
        }
        
        return response()->json(['message' => 'Booking status updated successfully']);
    }
}
