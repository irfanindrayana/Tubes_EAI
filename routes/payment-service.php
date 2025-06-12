<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PaymentApiController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Payment Service Routes
|--------------------------------------------------------------------------
|
| Routes specific to Payment Service
| These routes handle payment processing, verification, and payment methods
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'service' => 'payment-service',
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'redis' => Redis::ping() ? 'connected' : 'disconnected'
    ]);
});

// Public payment information
Route::prefix('payments')->group(function () {
    Route::get('/methods', [PaymentController::class, 'paymentMethods']);
    Route::get('/verify/{paymentCode}', [PaymentController::class, 'verifyPayment']);
});

// Authenticated payment routes
Route::middleware(['auth:sanctum'])->prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'myPayments']);
    Route::post('/', [PaymentController::class, 'processPayment']);
    Route::get('/{payment}', [PaymentController::class, 'paymentDetails']);
    Route::post('/{payment}/upload-proof', [PaymentController::class, 'uploadPaymentProof']);
    Route::get('/booking/{booking}', [PaymentController::class, 'getBookingPayment']);
});

// Internal API routes for inter-service communication
Route::prefix('api/v1/internal')->middleware(['api'])->group(function () {
    Route::prefix('payments')->group(function () {
        Route::post('/', [PaymentApiController::class, 'processPayment']);
        Route::get('/{paymentId}', [PaymentApiController::class, 'getPayment']);
        Route::put('/{paymentId}/status', [PaymentApiController::class, 'updatePaymentStatus']);
        Route::get('/verify/{paymentCode}', [PaymentApiController::class, 'verifyPayment']);
        Route::get('/methods', [PaymentApiController::class, 'getPaymentMethods']);
        Route::get('/user/{userId}/payments', [PaymentApiController::class, 'getUserPayments']);
        Route::get('/booking/{bookingId}/payment', [PaymentApiController::class, 'getBookingPayment']);
    });
});

// Admin routes (if this service instance is designated for admin)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/payments')->group(function () {
    Route::get('/', [PaymentController::class, 'adminPayments']);
    Route::get('/pending', [PaymentController::class, 'pendingPayments']);
    Route::put('/{payment}/verify', [PaymentController::class, 'verifyPaymentAdmin']);
    Route::put('/{payment}/reject', [PaymentController::class, 'rejectPayment']);
    
    // Payment method management
    Route::resource('methods', 'PaymentMethodController');
});
