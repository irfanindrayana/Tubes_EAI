<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| API Gateway Routes
|--------------------------------------------------------------------------
|
| Routes specific to API Gateway Service
| These routes handle main application routing, authentication, and
| coordination between microservices
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'service' => 'api-gateway',
        'status' => 'healthy',
        'timestamp' => now(),
        'services' => [
            'user-service' => Http::get(env('USER_SERVICE_URL') . '/health')->successful(),
            'ticketing-service' => Http::get(env('TICKETING_SERVICE_URL') . '/health')->successful(),
            'payment-service' => Http::get(env('PAYMENT_SERVICE_URL') . '/health')->successful(),
            'inbox-service' => Http::get(env('INBOX_SERVICE_URL') . '/health')->successful(),
        ]
    ]);
});

// Landing page
Route::get('/', function () {
    session()->start();
    
    if (Auth::check() && Auth::user()) {
        try {
            if (Auth::user()->isAdmin()) {
                return redirect('/home');
            } else {
                return redirect('/dashboard');
            }
        } catch (\Exception $e) {
            Auth::logout();
            return view('microservices-landing');
        }
    }
    
    return view('microservices-landing');
});

// Authentication routes (delegated to user service)
Route::prefix('auth')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

// Dashboard routes (protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

// Admin routes (protected)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::get('/routes', [AdminController::class, 'routes'])->name('routes');
    Route::get('/complaints', [AdminController::class, 'complaints'])->name('complaints');
});

// API proxy routes to microservices
Route::prefix('api/v1')->middleware(['api'])->group(function () {
    // User service proxy
    Route::prefix('users')->group(function () {
        Route::any('{any}', function ($path = null) {
            $url = env('USER_SERVICE_URL') . '/api/v1/users/' . $path;
            return Http::withHeaders(request()->headers->all())
                ->send(request()->method(), $url, request()->all());
        })->where('any', '.*');
    });

    // Ticketing service proxy
    Route::prefix('ticketing')->group(function () {
        Route::any('{any}', function ($path = null) {
            $url = env('TICKETING_SERVICE_URL') . '/api/v1/ticketing/' . $path;
            return Http::withHeaders(request()->headers->all())
                ->send(request()->method(), $url, request()->all());
        })->where('any', '.*');
    });

    // Payment service proxy
    Route::prefix('payments')->group(function () {
        Route::any('{any}', function ($path = null) {
            $url = env('PAYMENT_SERVICE_URL') . '/api/v1/payments/' . $path;
            return Http::withHeaders(request()->headers->all())
                ->send(request()->method(), $url, request()->all());
        })->where('any', '.*');
    });

    // Inbox service proxy
    Route::prefix('inbox')->group(function () {
        Route::any('{any}', function ($path = null) {
            $url = env('INBOX_SERVICE_URL') . '/api/v1/inbox/' . $path;
            return Http::withHeaders(request()->headers->all())
                ->send(request()->method(), $url, request()->all());
        })->where('any', '.*');
    });

    // Reviews service proxy
    Route::prefix('reviews')->group(function () {
        Route::any('{any}', function ($path = null) {
            $url = env('REVIEWS_SERVICE_URL') . '/api/v1/reviews/' . $path;
            return Http::withHeaders(request()->headers->all())
                ->send(request()->method(), $url, request()->all());
        })->where('any', '.*');
    });
});

// GraphQL endpoint
Route::post('/graphql', '\Nuwave\Lighthouse\Http\GraphQLController@query')->name('graphql');
Route::get('/graphql-playground', '\Nuwave\Lighthouse\Http\GraphQLPlaygroundController@get')->name('graphql-playground');
