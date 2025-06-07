<?php

require_once 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Bootstrap Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING SCHEDULE ISSUES ===\n\n";

// Check if there are schedules with empty days_of_week
$emptyDaysSchedules = \App\Models\Schedule::whereNull('days_of_week')
    ->orWhere('days_of_week', '[]')
    ->orWhere('days_of_week', '')
    ->count();

echo "Schedules with empty days_of_week: {$emptyDaysSchedules}\n";

// Check total schedules
$totalSchedules = \App\Models\Schedule::count();
echo "Total schedules: {$totalSchedules}\n\n";

// Check current data types and sample data
echo "Sample schedule analysis:\n";
$sampleSchedule = \App\Models\Schedule::first();
if ($sampleSchedule) {
    echo "Sample Schedule ID: {$sampleSchedule->id}\n";
    echo "Departure time raw: {$sampleSchedule->departure_time}\n";
    echo "Departure time formatted: " . \Carbon\Carbon::parse($sampleSchedule->departure_time)->format('H:i') . "\n";
    echo "Days of week: " . json_encode($sampleSchedule->days_of_week) . "\n";
    echo "Is array: " . (is_array($sampleSchedule->days_of_week) ? 'Yes' : 'No') . "\n";
}

echo "\n=== PROPOSED SOLUTIONS ===\n";

echo "1. Fix empty days_of_week by setting default to operate daily [0,1,2,3,4,5,6]\n";
echo "2. Update Schedule model to handle datetime properly\n";
echo "3. Update controller logic to handle travel_date filtering correctly\n";

echo "\n=== END ANALYSIS ===\n";
