<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserApiController;
use App\Http\Controllers\Api\V1\TicketingApiController;

/*
|--------------------------------------------------------------------------
| Internal API Routes
|--------------------------------------------------------------------------
|
| These routes are used for internal communication between microservices.
| They provide clean API endpoints for cross-service data access.
|
*/

Route::prefix('api/v1/internal')->middleware(['api'])->group(function () {
    
    // User Management Service API
    Route::prefix('users')->group(function () {
        Route::get('{userId}', [UserApiController::class, 'show']);
        Route::post('multiple', [UserApiController::class, 'getMultiple']);
        Route::get('{userId}/exists', [UserApiController::class, 'exists']);
        Route::get('{userId}/basic-info', [UserApiController::class, 'basicInfo']);
    });
    
    // Ticketing Service API
    Route::prefix('ticketing')->group(function () {
        Route::get('routes/{routeId}', [TicketingApiController::class, 'getRoute']);
        Route::get('schedules/{scheduleId}', [TicketingApiController::class, 'getSchedule']);
        Route::post('seats/availability', [TicketingApiController::class, 'checkSeatAvailability']);
        Route::post('bookings', [TicketingApiController::class, 'createBooking']);
        Route::get('bookings/{bookingId}', [TicketingApiController::class, 'getBooking']);
        Route::put('bookings/{bookingId}/status', [TicketingApiController::class, 'updateBookingStatus']);
    });
    
});
