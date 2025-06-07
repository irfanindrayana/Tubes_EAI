<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Message;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\FinancialReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show all users.
     */
    public function users(Request $request)
    {
        $query = User::with(['bookings', 'payments'])
            ->withCount(['bookings', 'payments']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(20);

        // Keep filters in pagination links
        $users->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.users.table', compact('users'))->render(),
                'pagination' => view('admin.users.pagination', compact('users'))->render()
            ]);
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Store a new user.
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,konsumen',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'email_verified_at' => now(), // Auto-verify admin created users
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully!'
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', 'User created successfully!');
    }

    /**
     * Show user details.
     */
    public function showUser(User $user)
    {
        $user->load(['bookings', 'payments']);
        $user->loadCount(['bookings', 'payments']);
        
        return response()->json($user);
    }

    /**
     * Show edit user form.
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user.
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,konsumen',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!'
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Delete user.
     */
    public function destroyUser(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully!'
        ]);
    }

    /**
     * Show all bookings.
     */
    public function bookings()
    {
        $bookings = Booking::with(['user', 'schedule.route', 'seat', 'payment'])
            ->latest()
            ->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Show complaints.
     */
    public function complaints()
    {
        $complaints = Message::where('message_type', 'complaint')
            ->with(['sender', 'recipients.user'])
            ->latest()
            ->paginate(15);

        return view('admin.complaints.index', compact('complaints'));
    }

    /**
     * Show routes management.
     */
    public function routes()
    {
        $routes = Route::withCount('schedules')
            ->latest()
            ->paginate(15);
            
        // Get all locations for form dropdowns
        $locations = Route::select('origin as city')
            ->union(Route::select('destination as city'))
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        // Get today's schedules for display
        $todaySchedules = Schedule::with('route')
            ->whereDate('departure_time', today())
            ->latest('departure_time')
            ->take(10)
            ->get();

        return view('admin.routes.index', compact('routes', 'locations', 'todaySchedules'));
    }
    
    /**
     * Show edit route form.
     */
    public function editRoute(Route $route)
    {
        return view('admin.routes.edit', compact('route'));
    }
    
    /**
     * Update route.
     */
    public function updateRoute(Route $route, Request $request)
    {
        $request->validate([
            'route_name' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'distance' => 'required|numeric|min:0',
            'estimated_duration' => 'required|numeric|min:1',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'stops' => 'nullable|array',
            'description' => 'nullable|string',
        ]);
        
        // Convert stops to JSON if provided
        if ($request->has('stops') && is_array($request->stops)) {
            // Filter out empty stops
            $stops = array_filter($request->stops, function($stop) {
                return !empty(trim($stop));
            });
            $request->merge(['stops' => json_encode($stops)]);
        }
        
        // Set is_active to false if not provided
        if (!$request->has('is_active')) {
            $request->merge(['is_active' => false]);
        }
        
        try {
            $route->update($request->all());
            return redirect()->route('admin.routes')
                ->with('success', 'Rute berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui rute: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Delete route.
     */
    public function deleteRoute(Route $route)
    {
        // Check if route has associated schedules
        if ($route->schedules()->count() > 0) {
            return redirect()->route('admin.routes')
                ->with('error', 'Tidak dapat menghapus rute yang memiliki jadwal terkait!');
        }
        
        try {
            $route->delete();
            return redirect()->route('admin.routes')
                ->with('success', 'Rute berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('admin.routes')
                ->with('error', 'Terjadi kesalahan saat menghapus rute: ' . $e->getMessage());
        }
    }

    /**
     * Show schedules management.
     */
    public function schedules()
    {
        $schedules = Schedule::with(['route'])
            ->withCount(['bookings', 'seats'])
            ->latest()
            ->paginate(20);

        return view('admin.schedules.index', compact('schedules'));
    }

    /**
     * Show financial reports.
     */
    public function reports()
    {
        // Get current month data
        $currentMonth = now()->format('Y-m');
        
        $monthlyRevenue = Payment::where('status', 'verified')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $totalBookings = Booking::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $totalUsers = User::where('role', 'konsumen')->count();

        // Get monthly revenue data for chart
        $monthlyData = Payment::where('status', 'verified')
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as revenue')
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('revenue', 'month');

        // Fill missing months with 0
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlyData->get($i, 0);
        }

        // Get top routes
        $topRoutes = Route::withCount('schedules')
            ->having('schedules_count', '>', 0)
            ->orderBy('schedules_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.reports.index', compact(
            'monthlyRevenue', 
            'totalBookings', 
            'totalUsers', 
            'chartData',
            'topRoutes'
        ));
    }

    /**
     * Create new route.
     */
    public function createRoute(Request $request)
    {
        $request->validate([
            'route_name' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'distance' => 'required|numeric|min:1',
            'estimated_duration' => 'required|numeric|min:1',
            'base_price' => 'required|numeric|min:0',
            'stops' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        // Convert stops to JSON if provided
        if ($request->has('stops') && is_array($request->stops)) {
            // Filter out empty stops
            $stops = array_filter($request->stops, function($stop) {
                return !empty(trim($stop));
            });
            $request->merge(['stops' => json_encode($stops)]);
        }
        
        // Set default is_active value if not provided
        if (!$request->has('is_active')) {
            $request->merge(['is_active' => false]);
        }
        
        try {
            Route::create($request->all());
            return redirect()->route('admin.routes')
                ->with('success', 'Rute berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menambahkan rute: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update route status.
     */
    public function updateRouteStatus(Route $route)
    {
        // Toggle the current status
        $route->update([
            'is_active' => !$route->is_active
        ]);

        $statusText = $route->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.routes')
            ->with('success', "Rute berhasil {$statusText}!");
    }

    /**
     * Create new schedule.
     */
    public function createSchedule(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:ticketing.routes,id',
            'operation_date' => 'required|date|after_or_equal:today',
            'departure_time' => 'required',
            'arrival_time' => 'nullable',
            'price' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1|max:100',
            'bus_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string'
        ]);

        // Create departure and arrival datetime
        $departureDateTime = $request->operation_date . ' ' . $request->departure_time;
        $arrivalDateTime = $request->arrival_time ? $request->operation_date . ' ' . $request->arrival_time : null;

        // Create the schedule
        $schedule = Schedule::create([
            'route_id' => $request->route_id,
            'departure_time' => $departureDateTime,
            'arrival_time' => $arrivalDateTime,
            'price' => $request->price,
            'total_seats' => $request->total_seats,
            'available_seats' => $request->total_seats,
            'bus_code' => $request->bus_number ?? 'BUS-' . rand(1000, 9999),
            'is_active' => $request->has('is_active')
        ]);

        // Create single schedule date
        $schedule->scheduleDates()->create([
            'scheduled_date' => $request->operation_date,
            'is_active' => true,
            'notes' => $request->notes
        ]);

        // Create seats for the operation date
        for ($i = 1; $i <= $request->total_seats; $i++) {
            $schedule->seats()->create([
                'seat_number' => sprintf('%02d', $i),
                'travel_date' => $request->operation_date,
                'status' => 'available'
            ]);
        }

        $message = 'Jadwal berhasil dibuat untuk tanggal ' . \Carbon\Carbon::parse($request->operation_date)->format('d M Y') . ' dengan ' . $request->total_seats . ' kursi!';

        return redirect()->route('admin.routes.schedules', $request->route_id)
            ->with('success', $message);
    }

    /**
     * Respond to complaint.
     */
    public function respondToComplaint(Message $complaint, Request $request)
    {
        $request->validate([
            'response' => 'required|string'
        ]);

        // Create response message
        Message::create([
            'sender_id' => Auth::id(),
            'subject' => 'Re: ' . $complaint->subject,
            'body' => $request->response,
            'message_type' => 'response',
            'status' => 'sent'
        ]);

        // Update complaint status
        $complaint->update(['status' => 'responded']);

        return redirect()->route('admin.complaints')
            ->with('success', 'Response sent successfully!');
    }

    /**
     * Show schedules for a specific route.
     */
    public function routeSchedules(Route $route)
    {
        $schedules = Schedule::where('route_id', $route->id)
            ->with(['scheduleDates' => function($query) {
                $query->where('is_active', true)
                      ->where('scheduled_date', '>=', today())
                      ->orderBy('scheduled_date');
            }])
            ->withCount(['bookings', 'seats'])
            ->with(['bookings' => function($query) {
                $query->whereIn('status', ['confirmed', 'completed']);
            }])
            ->orderBy('departure_time')
            ->paginate(15);
            
        // Calculate additional properties for each schedule
        foreach ($schedules as $schedule) {
            // Ensure bookings_count represents actual bookings (not cancelled ones)
            $schedule->bookings_count = $schedule->bookings->count();
            
            // Ensure capacity is always available
            $schedule->capacity = $schedule->total_seats;
        }
        
        return view('admin.routes.schedules', compact('route', 'schedules'));
    }

    /**
     * Get schedule details as JSON for AJAX requests.
     */
    public function getScheduleDetails(Schedule $schedule)
    {
        $schedule->load('route', 'bookings', 'scheduleDates');
        $bookings_count = $schedule->bookings()->count();
        $schedule->bookings_count = $bookings_count;
        
        // Format waktu untuk konsistensi frontend
        $schedule->departure_time_formatted = $schedule->departure_time ? $schedule->departure_time->format('H:i') : null;
        $schedule->arrival_time_formatted = $schedule->arrival_time ? $schedule->arrival_time->format('H:i') : null;
        
        // Memastikan bus number/code konsisten
        if (!$schedule->bus_number && $schedule->bus_code) {
            $schedule->bus_number = $schedule->bus_code;
        } elseif (!$schedule->bus_code && $schedule->bus_number) {
            $schedule->bus_code = $schedule->bus_number;
        }
        
        return response()->json($schedule);
    }

    /**
     * Show edit schedule form.
     */
    public function editSchedule(Schedule $schedule)
    {
        $routes = Route::where('is_active', true)->get();
        $schedule->bookings_count = $schedule->bookings()->count();
        
        return view('admin.schedules.edit', compact('schedule', 'routes'));
    }

    /**
     * Update a schedule.
     */
    public function updateSchedule(Schedule $schedule, Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:ticketing.routes,id',
            'departure_time' => 'required',
            'arrival_time' => 'nullable|after:departure_time',
            'price' => 'required|numeric|min:0',
            'bus_number' => 'nullable|string|max:20',
            'operation_date' => 'required|date|after_or_equal:today',
        ]);
        
        // Update basic schedule info
        $updateData = [
            'route_id' => $request->route_id,
            'departure_time' => $request->operation_date . ' ' . $request->departure_time,
            'arrival_time' => $request->arrival_time ? $request->operation_date . ' ' . $request->arrival_time : null,
            'price' => $request->price,
            'bus_code' => $request->bus_number ?? $schedule->bus_code,
            'is_active' => $request->has('is_active')
        ];
        
        // Delete existing schedule dates
        $schedule->scheduleDates()->delete();
        
        // Create new single schedule date
        $schedule->scheduleDates()->create([
            'scheduled_date' => $request->operation_date,
            'is_active' => true
        ]);
        
        $schedule->update($updateData);
        
        return redirect()->route('admin.routes.schedules', $schedule->route_id)
            ->with('success', 'Jadwal berhasil diperbarui!');
    }

    /**
     * Toggle schedule active status.
     */
    public function toggleScheduleStatus(Schedule $schedule)
    {
        $schedule->update([
            'is_active' => !$schedule->is_active
        ]);
        
        $statusText = $schedule->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()
            ->with('success', "Jadwal berhasil {$statusText}!");
    }
}
