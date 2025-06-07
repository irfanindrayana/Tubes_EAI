<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class MicroserviceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Handle cross-database relationships
        $this->setupCrossDatabaseRelationships();
        
        // Log database connections for debugging
        if (config('app.debug')) {
            $this->logDatabaseConnections();
        }
    }

    /**
     * Setup cross-database relationships for microservices
     */
    protected function setupCrossDatabaseRelationships()
    {
        // Configure how different microservices communicate
        // This can include event listeners, queues, or API calls
        
        // Example: When a booking is created, notify payment service
        // Event::listen(BookingCreated::class, NotifyPaymentService::class);
    }

    /**
     * Log active database connections for debugging
     */
    protected function logDatabaseConnections()
    {
        $connections = [
            'default' => config('database.default'),
            'user_management' => 'user_management',
            'ticketing' => 'ticketing',
            'payment' => 'payment',
            'reviews' => 'reviews',
            'inbox' => 'inbox',
        ];

        foreach ($connections as $name => $connection) {
            try {
                DB::connection($connection)->getPdo();
                logger()->info("✅ {$name} database connection successful");
            } catch (\Exception $e) {
                logger()->warning("❌ {$name} database connection failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Get microservice status for dashboard
     */
    public static function getMicroserviceStatus()
    {
        $services = [
            'user_management' => [
                'name' => 'User Management Service',
                'database' => 'transbandung_users',
                'status' => 'active',
                'models' => ['User', 'UserProfile'],
            ],
            'ticketing' => [
                'name' => 'Ticketing Service',
                'database' => 'transbandung_ticketing',
                'status' => 'active',
                'models' => ['Route', 'Schedule', 'Seat', 'Booking'],
            ],
            'payment' => [
                'name' => 'Payment Service',
                'database' => 'transbandung_payments',
                'status' => 'active',
                'models' => ['Payment', 'PaymentMethod'],
            ],
            'reviews' => [
                'name' => 'Review & Rating Service',
                'database' => 'transbandung_reviews',
                'status' => 'active',
                'models' => ['Review', 'Complaint'],
            ],
            'inbox' => [
                'name' => 'Inbox Service',
                'database' => 'transbandung_inbox',
                'status' => 'active',
                'models' => ['Message', 'MessageRecipient', 'Notification'],
            ],
        ];

        return $services;
    }
}
