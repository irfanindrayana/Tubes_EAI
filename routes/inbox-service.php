<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\InboxApiController;
use App\Http\Controllers\InboxController;

/*
|--------------------------------------------------------------------------
| Inbox Service Routes
|--------------------------------------------------------------------------
|
| Routes specific to Inbox Service
| These routes handle messaging, notifications, and communication
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'service' => 'inbox-service',
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'redis' => Redis::ping() ? 'connected' : 'disconnected'
    ]);
});

// Authenticated inbox routes
Route::middleware(['auth:sanctum'])->prefix('inbox')->group(function () {
    Route::get('/', [InboxController::class, 'index']);
    Route::get('/messages', [InboxController::class, 'messages']);
    Route::post('/messages', [InboxController::class, 'sendMessage']);
    Route::get('/messages/{message}', [InboxController::class, 'show']);
    Route::put('/messages/{message}/read', [InboxController::class, 'markAsRead']);
    Route::delete('/messages/{message}', [InboxController::class, 'deleteMessage']);
    
    // Notifications
    Route::get('/notifications', [InboxController::class, 'notifications']);
    Route::put('/notifications/{notification}/read', [InboxController::class, 'markNotificationAsRead']);
    Route::get('/unread-count', [InboxController::class, 'getUnreadCount']);
});

// Internal API routes for inter-service communication
Route::prefix('api/v1/internal')->middleware(['api'])->group(function () {
    Route::prefix('inbox')->group(function () {
        Route::post('/messages', [InboxApiController::class, 'sendMessage']);
        Route::get('/user/{userId}/messages', [InboxApiController::class, 'getUserMessages']);
        Route::put('/messages/{messageId}/read', [InboxApiController::class, 'markAsRead']);
        Route::post('/notifications', [InboxApiController::class, 'sendNotification']);
        Route::get('/user/{userId}/notifications', [InboxApiController::class, 'getUserNotifications']);
        Route::get('/user/{userId}/unread-count', [InboxApiController::class, 'getUnreadCount']);
        
        // Bulk operations
        Route::post('/messages/bulk', [InboxApiController::class, 'sendBulkMessages']);
        Route::post('/notifications/bulk', [InboxApiController::class, 'sendBulkNotifications']);
    });
});

// Admin routes (if this service instance is designated for admin)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/inbox')->group(function () {
    Route::get('/messages', [InboxController::class, 'adminMessages']);
    Route::get('/notifications', [InboxController::class, 'adminNotifications']);
    Route::post('/broadcast', [InboxController::class, 'broadcastMessage']);
    Route::get('/statistics', [InboxController::class, 'getStatistics']);
});
