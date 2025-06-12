<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Contracts\PaymentServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Payment API Controller
 * Handles all payment-related operations for microservice communication
 */
class PaymentApiController extends Controller
{
    protected PaymentServiceInterface $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Process a payment
     */
    public function processPayment(Request $request): JsonResponse
    {
        $paymentData = $request->validate([
            'booking_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_details' => 'array'
        ]);

        try {
            $result = $this->paymentService->processPayment($paymentData);
            return response()->json(['data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get payment by ID
     */
    public function show(int $paymentId): JsonResponse
    {
        $payment = $this->paymentService->getPayment($paymentId);
        
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        
        return response()->json(['data' => $payment]);
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_code' => 'required|string'
        ]);

        try {
            $result = $this->paymentService->verifyPayment($request->payment_code);
            return response()->json(['data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment verification failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods(): JsonResponse
    {
        try {
            $methods = $this->paymentService->getPaymentMethods();
            return response()->json(['data' => $methods]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve payment methods: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update payment status
     */
    public function updateStatus(Request $request, int $paymentId): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,completed,failed,cancelled'
        ]);

        try {
            $result = $this->paymentService->updatePaymentStatus($paymentId, $request->status);
            
            if (!$result) {
                return response()->json(['error' => 'Failed to update payment status'], 400);
            }
            
            return response()->json(['message' => 'Payment status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Status update failed: ' . $e->getMessage()], 500);
        }
    }
}
