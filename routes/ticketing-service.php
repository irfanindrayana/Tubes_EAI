<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TicketingApiController;
use App\Http\Controllers\TicketingController;

/*
|--------------------------------------------------------------------------
| Ticketing Service Routes
|--------------------------------------------------------------------------
|
| Routes specific to Ticketing Service
| These routes handle routes, schedules, bookings, and seat management
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'service' => 'ticketing-service',
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'redis' => Redis::ping() ? 'connected' : 'disconnected'
    ]);
});

// Public ticketing routes
Route::prefix('ticketing')->group(function () {
    // Route search and schedules
    Route::get('/routes', [TicketingController::class, 'routes']);
    Route::get('/routes/{route}', [TicketingController::class, 'routeDetails']);
    Route::get('/schedules', [TicketingController::class, 'schedules']);
    Route::get('/schedules/{schedule}', [TicketingController::class, 'scheduleDetails']);
    
    // Seat selection
    Route::get('/schedules/{schedule}/seats', [TicketingController::class, 'seats']);
    Route::post('/check-availability', [TicketingController::class, 'checkAvailability']);
});

// Authenticated booking routes
Route::middleware(['auth:sanctum'])->prefix('bookings')->group(function () {
    Route::get('/', [TicketingController::class, 'myBookings']);
    Route::post('/', [TicketingController::class, 'processBooking']);
    Route::get('/{booking}', [TicketingController::class, 'bookingDetails']);
    Route::put('/{booking}/cancel', [TicketingController::class, 'cancelBooking']);
});

// Internal API routes for inter-service communication
Route::prefix('api/v1/internal')->middleware(['api'])->group(function () {
    Route::prefix('ticketing')->group(function () {
        Route::get('routes/{routeId}', [TicketingApiController::class, 'getRoute']);
        Route::get('schedules/{scheduleId}', [TicketingApiController::class, 'getSchedule']);
        Route::post('seats/availability', [TicketingApiController::class, 'checkSeatAvailability']);
        Route::post('bookings', [TicketingApiController::class, 'createBooking']);
        Route::get('bookings/{bookingId}', [TicketingApiController::class, 'getBooking']);
        Route::put('bookings/{bookingId}/status', [TicketingApiController::class, 'updateBookingStatus']);
        Route::get('user/{userId}/bookings', [TicketingApiController::class, 'getUserBookings']);
    });
});

// Admin routes (if this service instance is designated for admin)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/ticketing')->group(function () {
    // Route management
    Route::resource('routes', 'RouteController');
    Route::resource('schedules', 'ScheduleController');
    
    // Booking management
    Route::get('/bookings', [TicketingController::class, 'adminBookings']);
    Route::put('/bookings/{booking}/confirm', [TicketingController::class, 'confirmBooking']);
    Route::put('/bookings/{booking}/cancel', [TicketingController::class, 'adminCancelBooking']);
});
