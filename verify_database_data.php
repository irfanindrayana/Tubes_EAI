<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Database and GraphQL Verification ===\n\n";

try {
    // Check database connection
    echo "1. Testing Database Connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "✓ Database connected successfully\n\n";
    
    // Check if we have any routes
    echo "2. Checking Routes Table...\n";
    $routes = DB::table('routes')->count();
    echo "Routes count: $routes\n";
    if ($routes > 0) {
        $sampleRoute = DB::table('routes')->first();
        echo "Sample route: " . json_encode($sampleRoute, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";
    
    // Check if we have any schedules
    echo "3. Checking Schedules Table...\n";
    $schedules = DB::table('schedules')->count();
    echo "Schedules count: $schedules\n";
    if ($schedules > 0) {
        $sampleSchedule = DB::table('schedules')->first();
        echo "Sample schedule: " . json_encode($sampleSchedule, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";
    
    // Check users
    echo "4. Checking Users Table...\n";
    $users = DB::table('users')->count();
    echo "Users count: $users\n";
    if ($users > 0) {
        $sampleUser = DB::table('users')->select('id', 'name', 'email', 'role')->first();
        echo "Sample user: " . json_encode($sampleUser, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== Verification Complete ===\n";
