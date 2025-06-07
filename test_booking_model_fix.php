<?php

require 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Booking;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== BOOKING MODEL RELATIONSHIP FIX TEST ===\n";

try {
    // Find the booking that was causing the error
    $booking = Booking::find(12);
    
    if (!$booking) {
        echo "❌ Booking #12 not found\n";
        exit(1);
    }
    
    echo "✅ Found booking #12: {$booking->booking_code}\n";
    echo "   Status: {$booking->status}\n";
    echo "   Seat count: {$booking->seat_count}\n";
    echo "   Travel date: {$booking->travel_date}\n\n";
    
    // Test the new accessor methods
    echo "🧪 Testing accessor methods:\n";
    
    // Test seat accessor
    echo "   Testing \$booking->seat...\n";
    $seat = $booking->seat;
    if ($seat) {
        echo "   ✅ seat accessor works: {$seat->seat_number}\n";
    } else {
        echo "   ⚠️  seat accessor returns null (expected for multi-seat bookings)\n";
    }
    
    // Test passenger_name accessor
    echo "   Testing \$booking->passenger_name...\n";
    $passengerName = $booking->passenger_name;
    if ($passengerName) {
        echo "   ✅ passenger_name accessor works: {$passengerName}\n";
    } else {
        echo "   ⚠️  passenger_name accessor returns null (expected for multi-seat bookings)\n";
    }
    
    // Test passenger_phone accessor
    echo "   Testing \$booking->passenger_phone...\n";
    $passengerPhone = $booking->passenger_phone;
    if ($passengerPhone) {
        echo "   ✅ passenger_phone accessor works: {$passengerPhone}\n";
    } else {
        echo "   ⚠️  passenger_phone accessor returns null (expected for multi-seat bookings)\n";
    }
    
    // Test total_price accessor
    echo "   Testing \$booking->total_price...\n";
    $totalPrice = $booking->total_price;
    if ($totalPrice) {
        echo "   ✅ total_price accessor works: Rp " . number_format($totalPrice, 0, ',', '.') . "\n";
    } else {
        echo "   ❌ total_price accessor failed\n";
    }
    
    echo "\n🧪 Testing array data access:\n";
    
    // Test seat_numbers array
    if (!empty($booking->seat_numbers)) {
        echo "   ✅ seat_numbers: " . implode(', ', $booking->seat_numbers) . "\n";
    } else {
        echo "   ❌ seat_numbers is empty\n";
    }
    
    // Test passenger_details array
    if (!empty($booking->passenger_details)) {
        echo "   ✅ passenger_details: " . count($booking->passenger_details) . " passenger(s)\n";
        foreach ($booking->passenger_details as $i => $passenger) {
            $seatNum = $booking->seat_numbers[$i] ?? $passenger['seat_number'] ?? 'N/A';
            echo "      - {$passenger['name']} ({$passenger['phone']}) - Seat {$seatNum}\n";
        }
    } else {
        echo "   ❌ passenger_details is empty\n";
    }
    
    echo "\n🧪 Testing relationship loading:\n";
    
    // Test schedule relationship
    try {
        $schedule = $booking->schedule;
        if ($schedule) {
            echo "   ✅ schedule relationship works: {$schedule->route->origin} → {$schedule->route->destination}\n";
        } else {
            echo "   ❌ schedule relationship failed\n";
        }
    } catch (Exception $e) {
        echo "   ❌ schedule relationship error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    echo "🎉 The booking model should now work without relationship errors!\n";
    echo "\nNext steps:\n";
    echo "1. Visit: http://127.0.0.1:8000/ticketing/booking-success/12\n";
    echo "2. The page should load without 'Call to undefined relationship [seat]' error\n";
    echo "3. Booking details should display correctly\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
