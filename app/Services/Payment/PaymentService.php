<?php

namespace App\Services\Payment;

use App\Contracts\PaymentServiceInterface;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Support\Str;

class PaymentService implements PaymentServiceInterface
{
    /**
     * Process payment
     */
    public function processPayment(array $paymentData): array
    {
        try {
            $payment = Payment::create([
                'payment_code' => $this->generatePaymentCode(),
                'booking_id' => $paymentData['booking_id'],
                'user_id' => $paymentData['user_id'],
                'payment_method_id' => $paymentData['payment_method_id'],
                'amount' => $paymentData['amount'],
                'status' => 'pending',
                'payment_proof' => $paymentData['payment_proof'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'payment_date' => now(),
            ]);
            
            return [
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'payment_code' => $payment->payment_code,
                    'booking_id' => $payment->booking_id,
                    'user_id' => $payment->user_id,
                    'payment_method_id' => $payment->payment_method_id,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get payment information
     */
    public function getPayment(int $paymentId): ?array
    {
        $payment = Payment::with('paymentMethod')->find($paymentId);
        
        if (!$payment) {
            return null;
        }
        
        return [
            'id' => $payment->id,
            'payment_code' => $payment->payment_code,
            'booking_id' => $payment->booking_id,
            'user_id' => $payment->user_id,
            'payment_method_id' => $payment->payment_method_id,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'payment_proof' => $payment->payment_proof,
            'notes' => $payment->notes,
            'payment_date' => $payment->payment_date,
            'verified_at' => $payment->verified_at,
            'payment_method' => $payment->paymentMethod ? [
                'id' => $payment->paymentMethod->id,
                'name' => $payment->paymentMethod->name,
                'type' => $payment->paymentMethod->type,
                'account_number' => $payment->paymentMethod->account_number,
                'account_name' => $payment->paymentMethod->account_name,
            ] : null,
        ];
    }
    
    /**
     * Verify payment status
     */
    public function verifyPayment(string $paymentCode): array
    {
        $payment = Payment::where('payment_code', $paymentCode)->first();
        
        if (!$payment) {
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }
        
        return [
            'success' => true,
            'payment' => [
                'id' => $payment->id,
                'payment_code' => $payment->payment_code,
                'booking_id' => $payment->booking_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'payment_date' => $payment->payment_date,
                'verified_at' => $payment->verified_at,
            ],
        ];
    }
    
    /**
     * Get payment methods
     */
    public function getPaymentMethods(): array
    {
        $methods = PaymentMethod::where('is_active', true)->get();
        
        return $methods->map(function ($method) {
            return [
                'id' => $method->id,
                'name' => $method->name,
                'type' => $method->type,
                'account_number' => $method->account_number,
                'account_name' => $method->account_name,
                'description' => $method->description,
                'logo' => $method->logo,
            ];
        })->toArray();
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $paymentId, string $status): bool
    {
        $payment = Payment::find($paymentId);
        
        if (!$payment) {
            return false;
        }
        
        $updateData = ['status' => $status];
        
        if ($status === 'verified') {
            $updateData['verified_at'] = now();
        }
        
        $payment->update($updateData);
        
        return true;
    }
    
    /**
     * Generate unique payment code
     */
    private function generatePaymentCode(): string
    {
        do {
            $code = 'PAY' . strtoupper(Str::random(8));
        } while (Payment::where('payment_code', $code)->exists());
        
        return $code;
    }
}
