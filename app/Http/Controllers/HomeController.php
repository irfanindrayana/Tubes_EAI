<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Route;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Complaint;
use App\Models\Message;
use App\Models\Notification;
use App\Providers\MicroserviceProvider;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get microservice status
        $microservices = MicroserviceProvider::getMicroserviceStatus();
        
        // Collect statistics for microservices dashboard
        $stats = [
            // User Management Service
            'total_users' => $this->safeCount(User::class),
            'admin_users' => $this->safeCount(User::class, ['is_admin' => true]),
            'active_sessions' => 1, // Simplified for demo
            
            // Ticketing Service
            'active_routes' => $this->safeCount(Route::class, ['status' => 'active']),
            'total_bookings' => $this->safeCount(Booking::class),
            'today_bookings' => $this->safeDateCount(Booking::class, Carbon::today()),
            
            // Payment Service
            'total_revenue' => $this->safeSum(Payment::class, 'amount', ['status' => 'completed']),
            'pending_payments' => $this->safeCount(Payment::class, ['status' => 'pending']),
            'today_payments' => $this->safeDateCount(Payment::class, Carbon::today(), ['status' => 'completed']),
            
            // Review & Rating Service
            'total_reviews' => $this->safeCount(Review::class),
            'average_rating' => $this->safeAverage(Review::class, 'rating'),
            'total_complaints' => $this->safeCount(Complaint::class),
            
            // Inbox Service
            'total_messages' => $this->safeCount(Message::class),
            'unread_messages' => $this->safeCount(Message::class, ['is_read' => false]),
            'total_notifications' => $this->safeCount(Notification::class),
            
            // GraphQL API stats (simplified)
            'total_queries' => 15,
            'total_mutations' => 12,
            'api_calls_today' => 0, // Could be tracked with middleware
        ];
        
        return view('home', compact('stats', 'microservices'));
    }

    /**
     * Safely count records from a model, handling database connection errors
     */
    private function safeCount($model, $where = [])
    {
        try {
            $query = $model::query();
            foreach ($where as $column => $value) {
                $query->where($column, $value);
            }
            return $query->count();
        } catch (\Exception $e) {
            logger()->warning("Failed to count {$model}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Safely count records by date
     */
    private function safeDateCount($model, $date, $where = [])
    {
        try {
            $query = $model::whereDate('created_at', $date);
            foreach ($where as $column => $value) {
                $query->where($column, $value);
            }
            return $query->count();
        } catch (\Exception $e) {
            logger()->warning("Failed to count {$model} by date: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Safely sum column values
     */
    private function safeSum($model, $column, $where = [])
    {
        try {
            $query = $model::query();
            foreach ($where as $col => $value) {
                $query->where($col, $value);
            }
            return $query->sum($column) ?: 0;
        } catch (\Exception $e) {
            logger()->warning("Failed to sum {$model}.{$column}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Safely calculate average
     */
    private function safeAverage($model, $column)
    {
        try {
            return $model::avg($column) ?: 0;
        } catch (\Exception $e) {
            logger()->warning("Failed to average {$model}.{$column}: " . $e->getMessage());
            return 0;
        }
    }
}
