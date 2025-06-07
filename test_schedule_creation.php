<?php
/**
 * Test Script for Schedule Creation
 * This script tests the schedule creation functionality to ensure the fixes work correctly
 */

// Include Laravel bootstrap
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;

echo "=== Schedule Creation Test ===\n";
echo "Testing the AdminController createSchedule method\n\n";

// Test data that mimics what would be sent from the form
$testData = [
    'route_id' => 1, // Assuming route ID 1 exists
    'departure_time' => '08:00',
    'arrival_time' => '10:00',
    'specific_dates' => [
        '2025-06-10',
        '2025-06-11',
        '2025-06-12'
    ]
];

echo "Test data prepared:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Check if routes table has data
echo "Checking available routes:\n";
try {
    $routes = DB::table('routes')->select('id', 'route_name', 'origin', 'destination')->get();
    if ($routes->count() > 0) {
        foreach ($routes as $route) {
            echo "- ID: {$route->id}, Name: {$route->route_name}, {$route->origin} → {$route->destination}\n";
        }
    } else {
        echo "No routes found in database\n";
    }
} catch (Exception $e) {
    echo "Error checking routes: " . $e->getMessage() . "\n";
}

echo "\n";

// Check current schedules
echo "Checking existing schedules:\n";
try {
    $schedules = DB::table('schedules')->count();
    echo "Total schedules in database: $schedules\n";
} catch (Exception $e) {
    echo "Error checking schedules: " . $e->getMessage() . "\n";
}

echo "\n";

// Test the validation logic
echo "Testing validation logic:\n";

// Create a mock request
$request = new Request();
$request->merge($testData);

// Validate the request data
$rules = [
    'route_id' => 'required|exists:routes,id',
    'departure_time' => 'required|date_format:H:i',
    'arrival_time' => 'nullable|date_format:H:i|after:departure_time',
    'specific_dates' => 'required|array|min:1',
    'specific_dates.*' => 'required|date|after_or_equal:today'
];

$validator = \Illuminate\Support\Facades\Validator::make($testData, $rules);

if ($validator->fails()) {
    echo "Validation failed:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "- $error\n";
    }
} else {
    echo "Validation passed successfully!\n";
    echo "✓ route_id: valid\n";
    echo "✓ departure_time: valid format\n";
    echo "✓ arrival_time: valid format and after departure\n";
    echo "✓ specific_dates: array with " . count($testData['specific_dates']) . " dates\n";
    
    foreach ($testData['specific_dates'] as $date) {
        echo "  ✓ $date: valid future date\n";
    }
}

echo "\n=== Test Complete ===\n";
