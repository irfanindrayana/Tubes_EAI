<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\ScheduleFixController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    // Force session start to ensure Auth::check() works correctly
    session()->start();
    
    // Check if user is authenticated and session is valid
    if (Auth::check() && Auth::user()) {
        try {
            if (Auth::user()->isAdmin()) {
                return redirect('/home');
            } else {
                return redirect('/dashboard');
            }
        } catch (\Exception $e) {
            // If there's an issue with the user session, log them out and show landing page
            Auth::logout();
            return view('microservices-landing');
        }
    }
    // For guests, show landing page
    return view('microservices-landing');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Debug Routes
Route::get('/debug/schedule/{id}', [DebugController::class, 'checkSchedule']);

// Schedule Fix Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function() {
    Route::get('/schedules/{schedule}/fix', [ScheduleFixController::class, 'showFixForm'])->name('schedules.fix');
    Route::post('/schedules/{schedule}/apply-fixes', [ScheduleFixController::class, 'applyFixes'])->name('schedules.apply-fixes');
});

// Dashboard Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Ticketing Routes
    Route::prefix('ticketing')->name('ticketing.')->group(function () {
        Route::get('/routes', [TicketingController::class, 'routes'])->name('routes');
        Route::get('/schedules/{route}', [TicketingController::class, 'schedules'])->name('schedules');
        Route::get('/seats/{schedule}', [TicketingController::class, 'seats'])->name('seats');
        Route::get('/booking/{schedule}/{seat}', [TicketingController::class, 'booking'])->name('booking');
        Route::get('/booking/{schedule}', [TicketingController::class, 'bookingMultiple'])->name('booking-multiple');
        Route::post('/booking', [TicketingController::class, 'processBooking'])->name('process-booking');
        Route::get('/booking-success/{booking}', [TicketingController::class, 'bookingSuccess'])->name('booking-success');
        Route::get('/my-bookings', [TicketingController::class, 'myBookings'])->name('my-bookings');
    });
    
    // Payment Routes
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/create/{booking}', [PaymentController::class, 'create'])->name('create');
        Route::post('/store', [PaymentController::class, 'store'])->name('store');
        Route::get('/status/{payment}', [PaymentController::class, 'status'])->name('status');
        Route::get('/my-payments', [PaymentController::class, 'myPayments'])->name('my-payments');
    });
    
    // Inbox Routes
    Route::prefix('inbox')->name('inbox.')->group(function () {
        Route::get('/', [InboxController::class, 'index'])->name('index');
        Route::get('/message/{message}', [InboxController::class, 'show'])->name('show');
        Route::post('/send', [InboxController::class, 'send'])->name('send');
        Route::post('/mark-as-read/{message}', [InboxController::class, 'markAsRead'])->name('mark-as-read');
    });
    
    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Users CRUD
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        
        Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
        
        // Routes CRUD
        Route::get('/routes', [AdminController::class, 'routes'])->name('routes');
        Route::post('/routes', [AdminController::class, 'createRoute'])->name('routes.store');
        Route::get('/routes/{route}/edit', [AdminController::class, 'editRoute'])->name('routes.edit');
        Route::put('/routes/{route}', [AdminController::class, 'updateRoute'])->name('routes.update');
        Route::delete('/routes/{route}', [AdminController::class, 'deleteRoute'])->name('routes.destroy');
        Route::patch('/routes/{route}/toggle', [AdminController::class, 'updateRouteStatus'])->name('routes.toggle');
        Route::get('/routes/{route}/schedules', [AdminController::class, 'routeSchedules'])->name('routes.schedules');
        
        // Schedules CRUD
        Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
        Route::post('/schedules', [AdminController::class, 'createSchedule'])->name('schedules.store');
        Route::get('/schedules/{schedule}', [AdminController::class, 'getScheduleDetails'])->name('schedules.show');
        Route::get('/schedules/{schedule}/edit', [AdminController::class, 'editSchedule'])->name('schedules.edit');
        Route::put('/schedules/{schedule}', [AdminController::class, 'updateSchedule'])->name('schedules.update');
        Route::patch('/schedules/{schedule}/toggle', [AdminController::class, 'toggleScheduleStatus'])->name('schedules.toggle');
        Route::get('/complaints', [AdminController::class, 'complaints'])->name('complaints');
        Route::post('/complaints/{complaint}/respond', [AdminController::class, 'respondToComplaint'])->name('complaints.respond');
        
        // Message reply functionality
        Route::post('/messages/{message}/reply', [InboxController::class, 'reply'])->name('messages.reply');
        
        // Message rating functionality 
        Route::post('/messages/{message}/rate', [InboxController::class, 'rate'])->name('messages.rate');
        
        // Payment verification
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/pending', [PaymentController::class, 'pendingPayments'])->name('pending');
            Route::put('/verify/{payment}', [PaymentController::class, 'verify'])->name('verify');
        });
    });
});
