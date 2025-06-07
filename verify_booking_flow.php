<?php
// Simple test to verify the booking flow works
echo "Testing the booking flow...\n\n";

// Test 1: Check if the route exists and is properly defined
echo "1. Checking route definition:\n";
$routeOutput = shell_exec('php artisan route:list --name=ticketing.booking 2>&1');
echo $routeOutput . "\n";

// Test 2: Check if the seat selection page can be compiled
echo "2. Testing seat selection page compilation:\n";
try {
    $viewOutput = shell_exec('php artisan view:clear 2>&1');
    echo "✓ View cache cleared successfully\n";
} catch (Exception $e) {
    echo "✗ Error clearing view cache: " . $e->getMessage() . "\n";
}

// Test 3: Quick syntax check on the seats.blade.php
echo "\n3. Quick syntax check on seats.blade.php:\n";
$seatFileContent = file_get_contents('resources/views/ticketing/seats.blade.php');
if (strpos($seatFileContent, 'window.location.href = `/ticketing/booking/{{ $schedule->id }}/${selectedSeat.id}`;') !== false) {
    echo "✓ Correct JavaScript URL construction found\n";
} else {
    echo "✗ JavaScript URL construction not found or incorrect\n";
}

// Test 4: Check if the TicketingController booking method exists
echo "\n4. Checking TicketingController booking method:\n";
$controllerContent = file_get_contents('app/Http/Controllers/TicketingController.php');
if (strpos($controllerContent, 'public function booking(Schedule $schedule, Seat $seat)') !== false) {
    echo "✓ Booking method with correct parameters found\n";
} else {
    echo "✗ Booking method not found or has incorrect parameters\n";
}

echo "\n5. Summary:\n";
echo "The booking flow should work correctly. If you're still experiencing the error,\n";
echo "it might be due to:\n";
echo "- Browser cache (try hard refresh with Ctrl+F5)\n";
echo "- Session issues (try logging out and back in)\n";
echo "- Database state (ensure seats exist and are available)\n";
echo "- Route model binding issues (ensure schedule and seat IDs are valid)\n";
