<?php

namespace App\GraphQL\Mutations;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Seat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class BookingMutation
{
    /**
     * Create a new booking.
     */
    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Booking
    {
        return DB::transaction(function () use ($args) {
            $user = Auth::user();
            $schedule = Schedule::findOrFail($args['schedule_id']);
            $seat = Seat::findOrFail($args['seat_id']);

            // Check if seat is available
            if (!$seat->is_available) {
                throw ValidationException::withMessages([
                    'seat_id' => ['This seat is no longer available.'],
                ]);
            }

            // Check if seat belongs to schedule
            if ($seat->schedule_id != $schedule->id) {
                throw ValidationException::withMessages([
                    'seat_id' => ['This seat does not belong to the selected schedule.'],
                ]);
            }

            // Check if schedule has available seats
            if ($schedule->available_seats <= 0) {
                throw ValidationException::withMessages([
                    'schedule_id' => ['No seats available for this schedule.'],
                ]);
            }            // Generate unique booking code
            $bookingCode = 'BTB-' . strtoupper(uniqid());

            // Create booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'booking_code' => $bookingCode,
                'travel_date' => $args['travel_date'] ?? now()->format('Y-m-d'),
                'seat_count' => 1,
                'seat_numbers' => [$seat->seat_number],
                'passenger_details' => [[
                    'name' => $args['passenger_name'],
                    'phone' => $args['passenger_phone'],
                    'seat_number' => $seat->seat_number
                ]],
                'total_amount' => $schedule->price,
                'status' => 'pending',
                'booking_date' => now(),
            ]);

            // Update seat availability
            $seat->update(['is_available' => false]);

            // Update schedule available seats
            $schedule->decrement('available_seats');

            return $booking->load(['user', 'schedule.route']);
        });
    }

    /**
     * Cancel a booking.
     */
    public function cancel($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Booking
    {
        return DB::transaction(function () use ($args) {
            $user = Auth::user();
            $booking = Booking::findOrFail($args['id']);

            // Check if user owns this booking or is admin
            if ($booking->user_id !== $user->id && !$user->isAdmin()) {
                throw new \Exception('Unauthorized to cancel this booking.');
            }

            // Check if booking can be cancelled
            if ($booking->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'id' => ['This booking is already cancelled.'],
                ]);
            }

            if ($booking->status === 'completed') {
                throw ValidationException::withMessages([
                    'id' => ['Cannot cancel a completed booking.'],
                ]);
            }            // Update booking status
            $booking->update(['status' => 'cancelled']);

            // Make seats available again
            if (!empty($booking->seat_numbers)) {
                $seatNumbers = $booking->seat_numbers;
                $seats = Seat::where('schedule_id', $booking->schedule_id)
                    ->whereIn('seat_number', $seatNumbers)
                    ->get();
                
                foreach ($seats as $seat) {
                    $seat->update(['is_available' => true]);
                }
            }

            // Update schedule available seats
            $booking->schedule->increment('available_seats', $booking->seat_count ?? 1);

            // Update payment status if exists
            if ($booking->payment) {
                $booking->payment->update(['status' => 'refunded']);
            }

            return $booking->load(['user', 'schedule.route', 'payment']);
        });
    }
}
