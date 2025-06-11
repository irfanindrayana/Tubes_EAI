<?php

namespace App\Contracts;

interface TicketingServiceInterface
{
    /**
     * Get route information
     */
    public function getRoute(int $routeId): ?array;
    
    /**
     * Get schedule information
     */
    public function getSchedule(int $scheduleId): ?array;
    
    /**
     * Check seat availability
     */
    public function checkSeatAvailability(int $scheduleId, string $date): array;
    
    /**
     * Create booking
     */
    public function createBooking(array $bookingData): array;
    
    /**
     * Get booking information
     */
    public function getBooking(int $bookingId): ?array;
    
    /**
     * Update booking status
     */
    public function updateBookingStatus(int $bookingId, string $status): bool;
}
