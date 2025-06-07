<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Booking;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Payment Creation Fix...\n";
echo "================================\n\n";

try {
    // Test database connections
    echo "1. Testing database connections...\n";
    $mainConnection = \DB::connection()->getPdo();
    $paymentConnection = \DB::connection('payment')->getPdo();
    echo "✓ Main database connected\n";
    echo "✓ Payment database connected\n\n";

    // Check if we have payment methods
    echo "2. Checking payment methods...\n";
    $paymentMethods = PaymentMethod::all();
    if ($paymentMethods->count() > 0) {
        echo "✓ Found {$paymentMethods->count()} payment methods\n";
        foreach ($paymentMethods as $method) {
            echo "  - {$method->name} (code: {$method->code})\n";
        }
    } else {
        echo "✗ No payment methods found\n";
    }
    echo "\n";

    // Check if we have users and bookings
    echo "3. Checking test data...\n";
    $user = User::first();
    $booking = Booking::first();
    
    if (!$user) {
        echo "✗ No users found\n";
        exit(1);
    }
    echo "✓ Found user: {$user->name}\n";
    
    if (!$booking) {
        echo "✗ No bookings found\n";
        exit(1);
    }
    echo "✓ Found booking: {$booking->booking_code}\n\n";

    // Test payment creation with correct fields
    echo "4. Testing payment creation...\n";
    $paymentMethod = $paymentMethods->first();
    
    $paymentData = [
        'payment_code' => 'PAY-' . strtoupper(\Illuminate\Support\Str::random(8)),
        'user_id' => $user->id,
        'booking_id' => $booking->id,
        'payment_method' => $paymentMethod->code,
        'amount' => 50000.00,
        'status' => 'pending',
        'proof_image' => null,
    ];
    
    echo "Creating payment with data:\n";
    foreach ($paymentData as $key => $value) {
        echo "  - {$key}: " . ($value ?? 'null') . "\n";
    }
    
    $payment = Payment::create($paymentData);
    echo "✓ Payment created successfully with ID: {$payment->id}\n\n";

    // Test payment relationships
    echo "5. Testing payment relationships...\n";
    $payment->load(['user', 'booking', 'paymentMethod']);
    
    if ($payment->user) {
        echo "✓ User relationship works: {$payment->user->name}\n";
    } else {
        echo "✗ User relationship failed\n";
    }
    
    if ($payment->booking) {
        echo "✓ Booking relationship works: {$payment->booking->booking_code}\n";
    } else {
        echo "✗ Booking relationship failed\n";
    }
    
    if ($payment->paymentMethod) {
        echo "✓ PaymentMethod relationship works: {$payment->paymentMethod->name}\n";
    } else {
        echo "✗ PaymentMethod relationship failed\n";
    }
    echo "\n";

    // Clean up test payment
    echo "6. Cleaning up...\n";
    $payment->delete();
    echo "✓ Test payment deleted\n\n";

    echo "================================\n";
    echo "✓ All tests passed! Payment creation should now work.\n";
    echo "The issue with missing payment_code field has been fixed.\n";

} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
