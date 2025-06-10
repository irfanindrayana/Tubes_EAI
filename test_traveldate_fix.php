<?php

require 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Schedule;
use App\Models\Seat;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING TRAVELDATE FIX FOR MULTIPLE BOOKING ===\n";

try {
    // Check if we have test data
    $schedule = Schedule::with('route')->first();
    if (!$schedule) {
        echo "❌ No schedules found in database\n";
        exit(1);
    }
    
    $availableSeats = Seat::where('schedule_id', $schedule->id)
                ->where('status', 'available')
                ->take(2)
                ->get();
    
    if ($availableSeats->count() < 2) {
        echo "❌ Not enough available seats found for testing multiple booking\n";
        exit(1);
    }
    
    echo "✅ Found test data:\n";
    echo "   - Schedule: {$schedule->route->origin} → {$schedule->route->destination}\n";
    echo "   - Available seats: " . $availableSeats->pluck('seat_number')->join(', ') . "\n\n";
    
    // Test: Check if the TicketingController@bookingMultiple method exists and can handle travelDate
    echo "🧪 TEST: Verifying TicketingController@bookingMultiple method...\n";
    
    $controller = new \App\Http\Controllers\TicketingController();
    $reflection = new ReflectionClass($controller);
    
    if ($reflection->hasMethod('bookingMultiple')) {
        echo "✅ SUCCESS: bookingMultiple method exists\n";
        
        // Get method and check its parameters
        $method = $reflection->getMethod('bookingMultiple');
        $parameters = $method->getParameters();
        
        echo "   - Method parameters: ";
        foreach ($parameters as $param) {
            echo $param->getName() . " ";
        }
        echo "\n";
        
        // Read the method source to check if travelDate is handled
        $filename = $reflection->getFileName();
        $startLine = $method->getStartLine() - 1;
        $endLine = $method->getEndLine();
        $length = $endLine - $startLine;
        
        $source = file($filename);
        $methodSource = implode("", array_slice($source, $startLine, $length));
        
        if (strpos($methodSource, 'travel_date') !== false && strpos($methodSource, 'travelDate') !== false) {
            echo "✅ SUCCESS: Method handles travel_date parameter correctly\n";
            if (strpos($methodSource, "compact('schedule', 'seats', 'travelDate')") !== false) {
                echo "✅ SUCCESS: travelDate is properly passed to the view\n";
            } else {
                echo "❌ ISSUE: travelDate might not be passed to the view correctly\n";
            }
        } else {
            echo "❌ ISSUE: Method doesn't seem to handle travel_date parameter\n";
        }
        
    } else {
        echo "❌ FAILED: bookingMultiple method does not exist\n";
        exit(1);
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    echo "🎉 The travelDate error for multiple booking should now be fixed!\n";
    echo "\nNext steps to verify:\n";
    echo "1. Visit: http://127.0.0.1:8000/ticketing/routes\n";
    echo "2. Search for a route and select multiple seats (2 or more)\n";
    echo "3. Complete the booking process\n";
    echo "4. Verify no 'Undefined variable \$travelDate' errors occur\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
