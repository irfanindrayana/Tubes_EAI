<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Since routing has been updated to direct admins to /home,
        // this method will only be accessed by regular users
        return $this->userDashboard();
    }

    /**
     * Admin dashboard data.
     */
    private function adminDashboard()
    {
        $stats = [
            'total_users' => User::where('role', 'konsumen')->count(),
            'total_bookings' => Booking::count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'total_revenue' => Payment::where('status', 'verified')->sum('amount'),
            'active_routes' => Route::where('is_active', true)->count(),
        ];

        $recentBookings = Booking::with(['user', 'schedule.route', 'payment'])
            ->latest()
            ->take(10)
            ->get();

        $pendingPayments = Payment::with(['user', 'booking.schedule.route'])
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentBookings', 'pendingPayments'));
    }

    /**
     * User dashboard data.
     */
    private function userDashboard()
    {
        $user = Auth::user();
        
        $recentBookings = Booking::with(['schedule.route', 'payment'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $upcomingTrips = Booking::with(['schedule.route'])
            ->where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->whereHas('schedule', function($query) {
                $query->where('departure_time', '>', now());
            })
            ->orderBy('created_at')
            ->take(3)
            ->get();

        // Try to get notifications, but handle database exceptions
        try {
            $notifications = Notification::where('user_id', $user->id)
                ->whereNull('read_at')  // Use read_at instead of is_read
                ->latest()
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            // If there's any database issue, just use an empty collection
            \Log::error('Error fetching notifications: ' . $e->getMessage());
            $notifications = collect();
        }

        return view('dashboard.user', compact('recentBookings', 'upcomingTrips', 'notifications'));
    }
}
