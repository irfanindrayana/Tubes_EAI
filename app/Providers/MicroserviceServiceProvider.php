<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\UserServiceInterface;
use App\Contracts\TicketingServiceInterface;
use App\Contracts\PaymentServiceInterface;
use App\Contracts\InboxServiceInterface;
use App\Services\UserManagement\UserService;
use App\Services\Ticketing\TicketingService;
use App\Services\Payment\PaymentService;
use App\Services\Inbox\InboxService;

class MicroserviceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind service interfaces to their implementations
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TicketingServiceInterface::class, TicketingService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(InboxServiceInterface::class, InboxService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
