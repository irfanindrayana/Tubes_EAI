<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Testing database connection...\n";
    $tables = DB::select("SHOW TABLES");
    echo "Connection successful! Found " . count($tables) . " tables:\n";
    
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        echo "- $tableName\n";
    }
    
    // Check if routes table exists
    $routes = collect($tables)->map(function($table) {
        return array_values((array)$table)[0];
    })->contains('routes');
    
    if ($routes) {
        echo "\nChecking routes table:\n";
        $routeCount = DB::table('routes')->count();
        echo "Routes count: $routeCount\n";
        
        if ($routeCount > 0) {
            $sampleRoutes = DB::table('routes')->limit(3)->get();
            echo "Sample routes:\n";
            foreach ($sampleRoutes as $route) {
                echo "- ID: {$route->id}, Name: " . ($route->route_name ?? 'N/A') . "\n";
            }
        }
    }
    
    // Check if schedules table exists
    $schedules = collect($tables)->map(function($table) {
        return array_values((array)$table)[0];
    })->contains('schedules');
    
    if ($schedules) {
        echo "\nChecking schedules table:\n";
        $scheduleCount = DB::table('schedules')->count();
        echo "Schedules count: $scheduleCount\n";
    }
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
