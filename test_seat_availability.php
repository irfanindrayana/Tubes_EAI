<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Seat;
use App\Models\Schedule;

echo "Testing Seat Availability Fix\n";
echo "============================\n\n";

// Check total seats
$totalSeats = Seat::count();
echo "Total seats in database: $totalSeats\n\n";

if ($totalSeats > 0) {
    // Get a sample of seats
    $seats = Seat::limit(10)->get(['id', 'seat_number', 'status', 'schedule_id']);
    
    echo "Sample seats:\n";
    foreach($seats as $seat) {
        echo "ID: {$seat->id}, Seat: {$seat->seat_number}, Status: {$seat->status}, Schedule: {$seat->schedule_id}\n";
    }
    
    echo "\nTesting is_available accessor:\n";
    foreach($seats as $seat) {
        $isAvailable = $seat->is_available ? 'true' : 'false';
        echo "Seat {$seat->seat_number}: status={$seat->status}, is_available={$isAvailable}\n";
    }
    
    // Check if there are available seats
    $availableCount = Seat::where('status', 'available')->count();
    $bookedCount = Seat::where('status', 'booked')->count();
    $reservedCount = Seat::where('status', 'reserved')->count();
    
    echo "\nSeat status breakdown:\n";
    echo "Available: $availableCount\n";
    echo "Booked: $bookedCount\n";
    echo "Reserved: $reservedCount\n";
    
    // Test with a specific schedule
    $schedule = Schedule::first();
    if ($schedule) {
        echo "\nTesting with Schedule ID {$schedule->id}:\n";
        $scheduleSeats = Seat::where('schedule_id', $schedule->id)->limit(5)->get();
        foreach($scheduleSeats as $seat) {
            $isAvailable = $seat->is_available ? 'true' : 'false';
            echo "  Seat {$seat->seat_number}: status={$seat->status}, is_available={$isAvailable}\n";
        }
    }
} else {
    echo "No seats found in database. Running seeder might be needed.\n";
    
    // Check if we have schedules
    $scheduleCount = Schedule::count();
    echo "Total schedules: $scheduleCount\n";
    
    if ($scheduleCount > 0) {
        echo "\nSchedules exist but no seats. Consider running:\n";
        echo "php artisan db:seed --class=RouteSeeder\n";
    }
}

echo "\n✓ Test completed!\n";
