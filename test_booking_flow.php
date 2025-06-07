<?php
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Route;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test if the booking route exists and is correctly defined
echo "Testing booking route...\n";

try {
    $routes = Route::getRoutes();
    $bookingRoute = null;
    
    foreach ($routes as $route) {
        if ($route->getName() === 'ticketing.booking') {
            $bookingRoute = $route;
            break;
        }
    }
    
    if ($bookingRoute) {
        echo "✓ Booking route found: " . $bookingRoute->uri() . "\n";
        echo "✓ Route parameters: " . implode(', ', $bookingRoute->parameterNames()) . "\n";
        echo "✓ Route methods: " . implode(', ', $bookingRoute->methods()) . "\n";
        
        // Test URL generation
        $testScheduleId = 1;
        $testSeatId = 1;
        
        try {
            $url = route('ticketing.booking', ['schedule' => $testScheduleId, 'seat' => $testSeatId]);
            echo "✓ Generated URL: $url\n";
        } catch (Exception $e) {
            echo "✗ Error generating URL: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "✗ Booking route not found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
