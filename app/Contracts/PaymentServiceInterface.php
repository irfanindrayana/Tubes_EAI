<?php

namespace App\Contracts;

interface PaymentServiceInterface
{
    /**
     * Process payment
     */
    public function processPayment(array $paymentData): array;
    
    /**
     * Get payment information
     */
    public function getPayment(int $paymentId): ?array;
    
    /**
     * Verify payment status
     */
    public function verifyPayment(string $paymentCode): array;
    
    /**
     * Get payment methods
     */
    public function getPaymentMethods(): array;
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $paymentId, string $status): bool;
}
