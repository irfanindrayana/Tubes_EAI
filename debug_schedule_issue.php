<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

use App\Models\Schedule;

echo "Debugging Schedule Data Issues...\n";
echo "================================\n\n";

// Get first 5 schedules to check their days_of_week format
$schedules = Schedule::take(5)->get();

foreach ($schedules as $schedule) {
    echo "Schedule ID: {$schedule->id}\n";
    echo "Route ID: {$schedule->route_id}\n";
    echo "Days of week type: " . gettype($schedule->days_of_week) . "\n";
    echo "Days of week value: " . var_export($schedule->days_of_week, true) . "\n";
    
    // Test JSON decode
    if (is_string($schedule->days_of_week)) {
        try {
            $decoded = json_decode($schedule->days_of_week, true);
            echo "JSON decode successful: " . var_export($decoded, true) . "\n";
        } catch (Exception $e) {
            echo "JSON decode failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Not a string, no JSON decode needed\n";
    }
    
    echo "---\n";
}

// Test a specific schedule from the route page if available
echo "\nTesting Admin Controller getScheduleDetails...\n";
$testSchedule = Schedule::first();
if ($testSchedule) {
    echo "Testing with Schedule ID: {$testSchedule->id}\n";
    
    // Simulate the controller logic
    $testSchedule->load('route', 'bookings');
    $bookings_count = $testSchedule->bookings()->count();
    $testSchedule->bookings_count = $bookings_count;
    
    // Check days_of_week handling
    if (is_string($testSchedule->days_of_week)) {
        try {
            $testSchedule->days_of_week = json_decode($testSchedule->days_of_week, true);
            echo "Controller logic worked fine\n";
        } catch (Exception $e) {
            echo "Controller logic failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "Final schedule data: " . json_encode($testSchedule, JSON_PRETTY_PRINT) . "\n";
}
