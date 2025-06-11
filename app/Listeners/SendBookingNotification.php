<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Contracts\InboxServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingNotification implements ShouldQueue
{
    use InteractsWithQueue;
    
    protected InboxServiceInterface $inboxService;

    /**
     * Create the event listener.
     */
    public function __construct(InboxServiceInterface $inboxService)
    {
        $this->inboxService = $inboxService;
    }

    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        $this->inboxService->sendNotification([
            'user_id' => $event->userId,
            'title' => 'Booking Confirmation',
            'content' => "Your booking #{$event->bookingData['booking_code']} has been created successfully.",
            'type' => 'booking',
        ]);
    }
}
