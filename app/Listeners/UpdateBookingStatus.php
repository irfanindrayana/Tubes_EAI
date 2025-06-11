<?php

namespace App\Listeners;

use App\Events\PaymentProcessed;
use App\Contracts\TicketingServiceInterface;
use App\Contracts\InboxServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBookingStatus implements ShouldQueue
{
    use InteractsWithQueue;
    
    protected TicketingServiceInterface $ticketingService;
    protected InboxServiceInterface $inboxService;

    /**
     * Create the event listener.
     */
    public function __construct(
        TicketingServiceInterface $ticketingService,
        InboxServiceInterface $inboxService
    ) {
        $this->ticketingService = $ticketingService;
        $this->inboxService = $inboxService;
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentProcessed $event): void
    {
        // Update booking status if payment is verified
        if ($event->paymentData['status'] === 'verified') {
            $this->ticketingService->updateBookingStatus($event->bookingId, 'confirmed');
            
            // Send confirmation notification
            $this->inboxService->sendNotification([
                'user_id' => $event->userId,
                'title' => 'Payment Confirmed',
                'content' => "Your payment for booking has been confirmed. Your ticket is now ready!",
                'type' => 'payment',
            ]);
        }
    }
}
