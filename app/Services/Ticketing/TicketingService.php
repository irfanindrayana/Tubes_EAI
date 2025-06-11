<?php

namespace App\Services\Ticketing;

use App\Contracts\TicketingServiceInterface;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TicketingService implements TicketingServiceInterface
{
    /**
     * Get route information
     */
    public function getRoute(int $routeId): ?array
    {
        $route = Route::find($routeId);
        
        if (!$route) {
            return null;
        }
        
        return [
            'id' => $route->id,
            'route_code' => $route->route_code,
            'origin' => $route->origin,
            'destination' => $route->destination,
            'distance' => $route->distance,
            'duration' => $route->duration,
            'price' => $route->price,
            'status' => $route->status,
        ];
    }
    
    /**
     * Get schedule information
     */
    public function getSchedule(int $scheduleId): ?array
    {
        $schedule = Schedule::with('route')->find($scheduleId);
        
        if (!$schedule) {
            return null;
        }
        
        return [
            'id' => $schedule->id,
            'route_id' => $schedule->route_id,
            'departure_time' => $schedule->departure_time,
            'arrival_time' => $schedule->arrival_time,
            'bus_number' => $schedule->bus_number,
            'capacity' => $schedule->capacity,
            'available_seats' => $schedule->available_seats,
            'price' => $schedule->price,
            'route' => $schedule->route ? [
                'id' => $schedule->route->id,
                'origin' => $schedule->route->origin,
                'destination' => $schedule->route->destination,
                'distance' => $schedule->route->distance,
                'duration' => $schedule->route->duration,
            ] : null,
        ];
    }
    
    /**
     * Check seat availability
     */
    public function checkSeatAvailability(int $scheduleId, string $date): array
    {
        $schedule = Schedule::find($scheduleId);
        
        if (!$schedule) {
            return ['available' => false, 'seats' => []];
        }
        
        // Get booked seats for this schedule and date
        $bookedSeats = Booking::where('schedule_id', $scheduleId)
            ->where('travel_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('seat_numbers')
            ->flatten()
            ->unique()
            ->values()
            ->toArray();
        
        // Generate available seats
        $totalSeats = $schedule->capacity;
        $availableSeats = [];
        
        for ($i = 1; $i <= $totalSeats; $i++) {
            if (!in_array($i, $bookedSeats)) {
                $availableSeats[] = $i;
            }
        }
        
        return [
            'available' => count($availableSeats) > 0,
            'total_capacity' => $totalSeats,
            'available_count' => count($availableSeats),
            'booked_count' => count($bookedSeats),
            'available_seats' => $availableSeats,
            'booked_seats' => $bookedSeats,
        ];
    }
    
    /**
     * Create booking
     */
    public function createBooking(array $bookingData): array
    {
        try {
            $booking = Booking::create([
                'booking_code' => $this->generateBookingCode(),
                'user_id' => $bookingData['user_id'],
                'schedule_id' => $bookingData['schedule_id'],
                'travel_date' => $bookingData['travel_date'],
                'seat_count' => $bookingData['seat_count'],
                'seat_numbers' => $bookingData['seat_numbers'],
                'passenger_details' => $bookingData['passenger_details'],
                'total_amount' => $bookingData['total_amount'],
                'status' => 'pending',
                'booking_date' => now(),
            ]);
            
            return [
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'user_id' => $booking->user_id,
                    'schedule_id' => $booking->schedule_id,
                    'travel_date' => $booking->travel_date,
                    'seat_count' => $booking->seat_count,
                    'seat_numbers' => $booking->seat_numbers,
                    'total_amount' => $booking->total_amount,
                    'status' => $booking->status,
                    'booking_date' => $booking->booking_date,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get booking information
     */
    public function getBooking(int $bookingId): ?array
    {
        $booking = Booking::with('schedule.route')->find($bookingId);
        
        if (!$booking) {
            return null;
        }
        
        return [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'user_id' => $booking->user_id,
            'schedule_id' => $booking->schedule_id,
            'travel_date' => $booking->travel_date,
            'seat_count' => $booking->seat_count,
            'seat_numbers' => $booking->seat_numbers,
            'passenger_details' => $booking->passenger_details,
            'total_amount' => $booking->total_amount,
            'status' => $booking->status,
            'booking_date' => $booking->booking_date,
            'schedule' => $booking->schedule ? [
                'id' => $booking->schedule->id,
                'departure_time' => $booking->schedule->departure_time,
                'arrival_time' => $booking->schedule->arrival_time,
                'bus_number' => $booking->schedule->bus_number,
                'route' => $booking->schedule->route ? [
                    'origin' => $booking->schedule->route->origin,
                    'destination' => $booking->schedule->route->destination,
                ] : null,
            ] : null,
        ];
    }
    
    /**
     * Update booking status
     */
    public function updateBookingStatus(int $bookingId, string $status): bool
    {
        $booking = Booking::find($bookingId);
        
        if (!$booking) {
            return false;
        }
        
        $booking->update(['status' => $status]);
        
        return true;
    }
    
    /**
     * Generate unique booking code
     */
    private function generateBookingCode(): string
    {
        do {
            $code = 'TB' . strtoupper(Str::random(8));
        } while (Booking::where('booking_code', $code)->exists());
        
        return $code;
    }
}
