<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Seat;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample users and schedules
        $users = User::where('role', 'konsumen')->take(10)->get();
        $schedules = Schedule::where('is_active', true)->take(8)->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        if ($users->isEmpty() || $schedules->isEmpty() || $paymentMethods->isEmpty()) {
            $this->command->error('Please run AdminUserSeeder, RouteSeeder, and PaymentMethodSeeder first!');
            return;
        }

        $bookingStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        $paymentStatuses = ['pending', 'verified', 'rejected'];

        foreach ($schedules as $schedule) {
            // Create 3-5 bookings per schedule
            $bookingCount = rand(3, 5);
            
            for ($i = 0; $i < $bookingCount; $i++) {
                $user = $users->random();
                $travelDate = now()->addDays(rand(0, 7))->format('Y-m-d');
                
                // Get available seats for this schedule and travel date
                $availableSeats = Seat::where('schedule_id', $schedule->id)
                    ->where('travel_date', $travelDate)
                    ->where('status', 'available')
                    ->take(rand(1, 3))
                    ->get();

                if ($availableSeats->isEmpty()) {
                    continue;
                }

                $seatCount = $availableSeats->count();
                $seatNumbers = $availableSeats->pluck('seat_number')->toArray();
                $totalAmount = $schedule->price * $seatCount;

                // Create booking
                $booking = Booking::create([
                    'booking_code' => 'TBA-' . strtoupper(Str::random(8)),
                    'user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'travel_date' => $travelDate,
                    'seat_count' => $seatCount,
                    'seat_numbers' => $seatNumbers,
                    'passenger_details' => [
                        [
                            'name' => $user->name,
                            'phone' => $user->phone ?: '08123456789',
                            'seat_number' => $seatNumbers[0]
                        ]
                    ],
                    'total_amount' => $totalAmount,
                    'status' => $bookingStatuses[array_rand($bookingStatuses)],
                    'booking_date' => now()->subHours(rand(1, 48)),
                ]);

                // Update seat status
                foreach ($availableSeats as $seat) {
                    $seat->update([
                        'status' => 'booked',
                        'booking_id' => $booking->id
                    ]);
                }

                // Create payment for this booking
                $paymentMethod = $paymentMethods->random();
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

                Payment::create([
                    'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'amount' => $totalAmount,
                    'payment_method' => 'transfer_bank', // This should match the enum in migration
                    'status' => $paymentStatus,
                    'proof_image' => $paymentStatus !== 'pending' ? 'payment_proofs/sample_transfer.jpg' : null,
                    'admin_notes' => $paymentStatus === 'verified' ? 'Payment verified successfully' : 
                                   ($paymentStatus === 'rejected' ? 'Invalid payment proof' : null),
                    'payment_date' => now()->subHours(rand(1, 24)),
                    'verified_at' => $paymentStatus === 'verified' ? now()->subHours(rand(1, 12)) : null,
                    'verified_by' => $paymentStatus === 'verified' ? User::where('role', 'admin')->first()->id : null,
                ]);
            }
        }

        $this->command->info('Bookings and payments created successfully!');
    }
}
