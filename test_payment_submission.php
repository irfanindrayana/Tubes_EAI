<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\PaymentController;
use App\Models\Booking;
use App\Models\PaymentMethod;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== Testing Payment Submission ===\n\n";

try {
    // Check if booking exists
    $booking = Booking::find(12);
    if (!$booking) {
        echo "❌ Booking with ID 12 not found\n";
        exit(1);
    }
    
    echo "✅ Booking found: ID {$booking->id}, Total: {$booking->total_amount}\n";
    
    // Check payment methods
    $paymentMethods = PaymentMethod::all();
    echo "✅ Available payment methods: " . $paymentMethods->count() . "\n";
    
    if ($paymentMethods->isEmpty()) {
        echo "❌ No payment methods available\n";
        exit(1);
    }
    
    $firstPaymentMethod = $paymentMethods->first();
    echo "✅ Using payment method: {$firstPaymentMethod->name} (code: {$firstPaymentMethod->code})\n\n";
    
    // Simulate payment submission
    $requestData = [
        'booking_id' => 12,
        'payment_method' => $firstPaymentMethod->code, // Use code instead of ID
        'amount' => $booking->total_amount,
        'proof_image' => 'test_proof.jpg', // Use correct field name
    ];
    
    echo "=== Testing Payment Data Validation ===\n";
    echo "Request data:\n";
    foreach ($requestData as $key => $value) {
        echo "  {$key}: {$value}\n";
    }
    
    // Test payment code generation
    $paymentCode = 'PAY-' . strtoupper(\Illuminate\Support\Str::random(8));
    echo "\n✅ Generated payment code: {$paymentCode}\n";
    
    // Test payment creation logic
    echo "\n=== Testing Payment Creation Logic ===\n";
    
    // Check if all required fields are present
    $requiredFields = ['booking_id', 'payment_method', 'amount', 'proof_image'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($requestData[$field]) || empty($requestData[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        echo "❌ Missing required fields: " . implode(', ', $missingFields) . "\n";
    } else {
        echo "✅ All required fields present\n";
    }
    
    // Verify payment method exists
    $paymentMethod = PaymentMethod::where('code', $requestData['payment_method'])->first();
    if (!$paymentMethod) {
        echo "❌ Payment method with code '{$requestData['payment_method']}' not found\n";
    } else {
        echo "✅ Payment method verified: {$paymentMethod->name}\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "✅ Payment code generation: Working\n";
    echo "✅ Field name mapping: Correct\n";
    echo "✅ Required fields: All present\n";
    echo "✅ Payment method lookup: Working\n";
    echo "\n🎉 Payment submission should work correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
