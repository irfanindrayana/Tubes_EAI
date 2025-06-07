<?php

require 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Seat;
use App\Models\User;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING TRAVEL DATE FIX ===\n";

try {
    // Check if we have test data
    $schedule = Schedule::with('route')->first();
    if (!$schedule) {
        echo "❌ No schedules found in database\n";
        exit(1);
    }
      $seat = Seat::where('schedule_id', $schedule->id)
                ->where('status', 'available')
                ->first();
    
    if (!$seat) {
        echo "❌ No available seats found for schedule {$schedule->id}\n";
        exit(1);
    }
    
    $user = User::first();
    if (!$user) {
        echo "❌ No users found in database\n";
        exit(1);
    }
    
    echo "✅ Found test data:\n";
    echo "   - Schedule: {$schedule->route->origin} → {$schedule->route->destination}\n";
    echo "   - Available seat: {$seat->seat_number}\n";
    echo "   - User: {$user->name}\n\n";
    
    // Test 1: Create a booking with travel_date
    echo "🧪 TEST 1: Creating booking with travel_date...\n";
    
    $travelDate = Carbon::tomorrow()->format('Y-m-d');
    $bookingCode = 'TEST-' . strtoupper(uniqid());
      $booking = Booking::create([
        'user_id' => $user->id,
        'schedule_id' => $schedule->id,
        'booking_code' => $bookingCode,
        'travel_date' => $travelDate,
        'seat_count' => 1,
        'seat_numbers' => [$seat->seat_number],
        'passenger_details' => [[
            'name' => 'Test Passenger',
            'phone' => '081234567890',
            'seat_number' => $seat->seat_number
        ]],
        'total_amount' => $schedule->price,
        'status' => 'pending',
        'booking_date' => now(),
    ]);
    
    if ($booking->id) {
        echo "✅ SUCCESS: Booking created successfully!\n";
        echo "   - Booking ID: {$booking->id}\n";
        echo "   - Booking Code: {$booking->booking_code}\n";
        echo "   - Travel Date: {$booking->travel_date}\n";
        echo "   - Status: {$booking->status}\n";
        
        // Clean up test data
        $booking->delete();
        echo "🧹 Test booking cleaned up\n\n";
    } else {
        echo "❌ FAILED: Could not create booking\n";
        exit(1);
    }
    
    // Test 2: Verify travel_date field is in fillable array
    echo "🧪 TEST 2: Checking Booking model fillable fields...\n";
    
    $bookingModel = new Booking();
    $fillable = $bookingModel->getFillable();
    
    if (in_array('travel_date', $fillable)) {
        echo "✅ SUCCESS: travel_date is in fillable array\n";
    } else {
        echo "❌ FAILED: travel_date is NOT in fillable array\n";
        echo "   Current fillable fields: " . implode(', ', $fillable) . "\n";
        exit(1);
    }
    
    echo "\n=== ALL TESTS PASSED ===\n";
    echo "🎉 The travel_date booking error should now be fixed!\n";
    echo "\nNext steps:\n";
    echo "1. Visit: http://127.0.0.1:8000/ticketing/routes\n";
    echo "2. Search for a route and select seats\n";
    echo "3. Complete the booking process\n";
    echo "4. Verify no SQL errors occur\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
