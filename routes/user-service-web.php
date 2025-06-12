<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| User Service Web Routes
|--------------------------------------------------------------------------
|
| Minimal web routes for User Service microservice
| These routes are required for Laravel framework to function properly
|
*/

// Basic health check route for web interface
Route::get('/', function () {
    return response()->json([
        'service' => 'user-service',
        'status' => 'healthy',
        'timestamp' => now(),
        'message' => 'User Management Service is running'
    ]);
});

// Minimal authentication routes if needed for Laravel framework
// Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);
