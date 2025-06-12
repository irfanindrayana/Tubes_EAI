<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ReviewsApiController;
use App\Http\Controllers\ReviewsController;

/*
|--------------------------------------------------------------------------
| Reviews Service Routes
|--------------------------------------------------------------------------
|
| Routes specific to Reviews Service
| These routes handle reviews, ratings, complaints, and feedback
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'service' => 'reviews-service',
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'redis' => Redis::ping() ? 'connected' : 'disconnected'
    ]);
});

// Public review endpoints
Route::prefix('reviews')->group(function () {
    Route::get('/', [ReviewsController::class, 'index']); // List reviews
    Route::get('/route/{routeId}', [ReviewsController::class, 'routeReviews']); // Reviews for specific route
    Route::get('/stats', [ReviewsController::class, 'stats']); // Review statistics
});

// Authenticated review routes
Route::middleware(['auth:sanctum'])->prefix('reviews')->group(function () {
    Route::post('/', [ReviewsController::class, 'store']); // Create review
    Route::get('/my-reviews', [ReviewsController::class, 'myReviews']); // User's reviews
    Route::put('/{review}', [ReviewsController::class, 'update']); // Update review
    Route::delete('/{review}', [ReviewsController::class, 'destroy']); // Delete review
    
    // Complaints
    Route::post('/complaints', [ReviewsController::class, 'submitComplaint']);
    Route::get('/complaints/my', [ReviewsController::class, 'myComplaints']);
});

// Internal API routes for inter-service communication
Route::prefix('api/v1/internal')->middleware(['api'])->group(function () {
    Route::prefix('reviews')->group(function () {
        Route::get('/route/{routeId}', [ReviewsApiController::class, 'getRouteReviews']);
        Route::get('/stats/{routeId}', [ReviewsApiController::class, 'getRouteStats']);
        Route::get('/user/{userId}', [ReviewsApiController::class, 'getUserReviews']);
        Route::post('/', [ReviewsApiController::class, 'createReview']);
        Route::put('/{reviewId}', [ReviewsApiController::class, 'updateReview']);
        Route::delete('/{reviewId}', [ReviewsApiController::class, 'deleteReview']);
        
        // Complaints
        Route::post('/complaints', [ReviewsApiController::class, 'createComplaint']);
        Route::get('/complaints/user/{userId}', [ReviewsApiController::class, 'getUserComplaints']);
        Route::get('/complaints/pending', [ReviewsApiController::class, 'getPendingComplaints']);
        Route::put('/complaints/{complaintId}/status', [ReviewsApiController::class, 'updateComplaintStatus']);
    });
});

// Admin routes (if this service instance is designated for admin)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/reviews')->group(function () {
    Route::get('/', [ReviewsController::class, 'adminReviews']);
    Route::get('/pending', [ReviewsController::class, 'pendingReviews']);
    Route::put('/{review}/moderate', [ReviewsController::class, 'moderateReview']);
    Route::delete('/{review}/admin-delete', [ReviewsController::class, 'adminDeleteReview']);
    
    // Complaint management
    Route::get('/complaints', [ReviewsController::class, 'adminComplaints']);
    Route::put('/complaints/{complaint}/resolve', [ReviewsController::class, 'resolveComplaint']);
    Route::put('/complaints/{complaint}/assign', [ReviewsController::class, 'assignComplaint']);
});
