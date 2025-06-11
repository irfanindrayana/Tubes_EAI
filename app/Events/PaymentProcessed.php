<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $paymentData;
    public int $bookingId;
    public int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(array $paymentData, int $bookingId, int $userId)
    {
        $this->paymentData = $paymentData;
        $this->bookingId = $bookingId;
        $this->userId = $userId;
    }
}
