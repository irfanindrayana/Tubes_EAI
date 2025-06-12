<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserApiController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| User Service Routes
|--------------------------------------------------------------------------
|
| Routes specific to User Management Service
| These routes handle user authentication, registration, and profile management
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'service' => 'user-service',
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'redis' => Redis::ping() ? 'connected' : 'disconnected'
    ]);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});

// User profile routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/avatar', [AuthController::class, 'uploadAvatar']);
});

// Internal API routes for inter-service communication
Route::prefix('api/v1/internal')->middleware(['api'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('{userId}', [UserApiController::class, 'show']);
        Route::post('multiple', [UserApiController::class, 'getMultiple']);
        Route::get('{userId}/exists', [UserApiController::class, 'exists']);
        Route::get('{userId}/basic-info', [UserApiController::class, 'basicInfo']);
        Route::get('{userId}/profile', [UserApiController::class, 'profile']);
    });
});

// Admin routes (if this service instance is designated as admin)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/users/{user}', [UserApiController::class, 'show']);
    Route::put('/users/{user}', [UserApiController::class, 'update']);
    Route::delete('/users/{user}', [UserApiController::class, 'destroy']);
});
