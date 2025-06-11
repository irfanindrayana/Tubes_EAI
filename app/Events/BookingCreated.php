<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $bookingData;
    public int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(array $bookingData, int $userId)
    {
        $this->bookingData = $bookingData;
        $this->userId = $userId;
    }
}
