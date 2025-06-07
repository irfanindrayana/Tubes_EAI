<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Seat;

echo "Seat Availability Accessor Test\n";
echo "===============================\n\n";

// Get first few seats
$seats = Seat::limit(5)->get();

echo "Testing is_available accessor:\n";
foreach($seats as $seat) {
    $status = $seat->status;
    $isAvailable = $seat->is_available;
    $expected = ($status === 'available') ? 'true' : 'false';
    $actual = $isAvailable ? 'true' : 'false';
    $result = ($expected === $actual) ? '✓ PASS' : '✗ FAIL';
    
    echo "Seat {$seat->seat_number}: status={$status}, is_available={$actual}, expected={$expected} {$result}\n";
}

// Test different statuses
echo "\nTesting different seat statuses:\n";

// Create a test seat with different statuses
$testStatuses = ['available', 'booked', 'reserved'];
foreach($testStatuses as $status) {
    // Find or create a seat with this status
    $seat = Seat::where('status', $status)->first();
    if (!$seat) {
        // Temporarily change a seat's status for testing
        $seat = Seat::first();
        $originalStatus = $seat->status;
        $seat->update(['status' => $status]);
        
        $isAvailable = $seat->fresh()->is_available;
        $expected = ($status === 'available');
        $actual = $isAvailable;
        $result = ($expected === $actual) ? '✓ PASS' : '✗ FAIL';
        
        echo "Status '{$status}': is_available=" . ($actual ? 'true' : 'false') . ", expected=" . ($expected ? 'true' : 'false') . " {$result}\n";
        
        // Restore original status
        $seat->update(['status' => $originalStatus]);
    } else {
        $isAvailable = $seat->is_available;
        $expected = ($status === 'available');
        $actual = $isAvailable;
        $result = ($expected === $actual) ? '✓ PASS' : '✗ FAIL';
        
        echo "Status '{$status}': is_available=" . ($actual ? 'true' : 'false') . ", expected=" . ($expected ? 'true' : 'false') . " {$result}\n";
    }
}

echo "\n✓ Test completed!\n";
