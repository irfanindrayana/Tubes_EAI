<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Seat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show routes page with matching schedules.
     */
    public function routes(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $travelDate = $request->input('travel_date');
        $seatCount = $request->input('seat_count', 1);
        
        // Map short codes to actual location names
        $locationMapping = [
            'bndng' => 'bndng', // Keep as is since database has this exact value
            'tngsl' => 'tngsel', // Map to correct spelling in database
            'leuwi' => 'Terminal Leuwi Panjang',
            'cicaheum' => 'Terminal Cicaheum',
            'caringin' => 'Terminal Caringin',
            'cimindi' => 'Terminal Cimindi',
        ];
        
        // Apply mapping if exact match not found
        if ($origin && isset($locationMapping[strtolower($origin)])) {
            $origin = $locationMapping[strtolower($origin)];
        }
        
        if ($destination && isset($locationMapping[strtolower($destination)])) {
            $destination = $locationMapping[strtolower($destination)];
        }
        
        // Get all available origins and destinations for dropdown
        $locations = Route::select('origin as city')
            ->union(Route::select('destination as city'))
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
        
        $query = Route::where('is_active', true);
        
        // Filter by origin
        if ($origin) {
            // Use exact match first, then fallback to LIKE for user-inputted values
            $originQuery = Route::where('origin', $origin);
            if ($originQuery->count() === 0) {
                $query->where('origin', 'like', '%' . $origin . '%');
            } else {
                $query->where('origin', $origin);
            }
        }
        
        // Filter by destination
        if ($destination) {
            // Use exact match first, then fallback to LIKE for user-inputted values
            $destinationQuery = Route::where('destination', $destination);
            if ($destinationQuery->count() === 0) {
                $query->where('destination', 'like', '%' . $destination . '%');
            } else {
                $query->where('destination', $destination);
            }
        }
        
        // Load schedules with filtering based on search parameters
        $query->with(['schedules' => function($scheduleQuery) use ($travelDate, $seatCount) {
            $scheduleQuery->where('is_active', true);
                
            if ($travelDate) {
                // Only filter by specific dates - no more recurring schedules
                $scheduleQuery->whereHas('scheduleDates', function($dateQuery) use ($travelDate) {
                    $dateQuery->where('scheduled_date', $travelDate)
                             ->where('is_active', true);
                });
            }
            
            if ($seatCount > 1) {
                $scheduleQuery->where('available_seats', '>=', $seatCount);
            }
            
            $scheduleQuery->with('scheduleDates')->orderBy('departure_time');
        }]);

        // Count matching schedules
        $query->withCount(['schedules' => function($scheduleQuery) use ($travelDate, $seatCount) {
            $scheduleQuery->where('is_active', true);
                
            if ($travelDate) {
                // Only filter by specific dates - no more recurring schedules
                $scheduleQuery->whereHas('scheduleDates', function($dateQuery) use ($travelDate) {
                    $dateQuery->where('scheduled_date', $travelDate)
                             ->where('is_active', true);
                });
            }
            
            if ($seatCount > 1) {
                $scheduleQuery->where('available_seats', '>=', $seatCount);
            }
        }]);
        
        $routes = $query->paginate(12)->appends([
            'origin' => $origin,
            'destination' => $destination,
            'travel_date' => $travelDate,
            'seat_count' => $seatCount,
        ]);

        // Process schedules to add status information
        foreach($routes as $route) {
            foreach($route->schedules as $schedule) {
                $status = 'unavailable';
                
                if ($schedule->is_active) {
                    // Check if schedule operates on the requested date
                    if ($travelDate) {
                        // For specific dates schedules only, check if the date is in scheduleDates
                        $operatesToday = $schedule->scheduleDates()
                            ->where('scheduled_date', $travelDate)
                            ->where('is_active', true)
                            ->exists();
                        
                        if ($operatesToday && $schedule->available_seats >= $seatCount) {
                            $status = 'available';
                        }
                    } else {
                        $status = 'scheduled';
                    }
                }
                
                $schedule->status = $status;
            }
        }

        return view('ticketing.routes', compact('routes', 'locations', 'origin', 'destination', 'travelDate', 'seatCount'));
    }

    /**
     * Show schedules for a route.
     */
    public function schedules(Route $route, Request $request)
    {
        $seatCount = $request->input('seat_count', 1);
        $travelDate = $request->input('travel_date');
        
        $schedules = Schedule::where('route_id', $route->id)
            ->where('departure_time', '>', now())
            ->with(['route', 'scheduleDates'])
            ->orderBy('departure_time')
            ->get();

        // Process each schedule to set the proper status flag
        foreach($schedules as $schedule) {
            // Default status is 'unavailable'
            $status = 'unavailable';
            
            // If schedule is active and has seats, it's 'scheduled'
            if ($schedule->is_active) {
                // For specific dates schedules only, check if any future dates are available
                $operatesToday = $schedule->scheduleDates()
                    ->where('scheduled_date', '>=', today())
                    ->where('is_active', true)
                    ->exists();
                
                if ($operatesToday) {
                    $status = 'scheduled';
                }
            }
            
            // Add status field to the schedule object
            $schedule->status = $status;
        }

        return view('ticketing.schedules', compact('route', 'schedules', 'seatCount', 'travelDate'));
    }

    /**
     * Show seat selection page.
     */
    public function seats(Schedule $schedule, Request $request)
    {
        $seats = Seat::where('schedule_id', $schedule->id)
            ->orderBy('seat_number')
            ->get();

        // Get seat count from request (from route search)
        $seatCount = $request->input('seat_count', 1);
        
        // Get travel date from request or use today as default
        $travelDate = $request->input('travel_date', now()->format('Y-m-d'));

        return view('ticketing.seats', compact('schedule', 'seats', 'seatCount', 'travelDate'));
    }

    /**
     * Show booking form.
     */
    public function booking(Schedule $schedule, Seat $seat = null, Request $request)
    {
        // Handle multiple seat booking
        if (!$seat && $request->has('seats')) {
            $seatIds = explode(',', $request->input('seats'));
            $seats = Seat::whereIn('id', $seatIds)->get();
            $travelDate = $request->input('travel_date', now()->format('Y-m-d'));
            
            // Check if all seats are available
            foreach ($seats as $seatItem) {
                if (!$seatItem->is_available) {
                    return redirect()->route('ticketing.seats', $schedule)
                        ->with('error', "Seat {$seatItem->seat_number} is no longer available.");
                }
            }
            
            return view('ticketing.booking-multiple', compact('schedule', 'seats', 'travelDate'));
        }
        
        // Handle single seat booking
        if (!$seat->is_available) {
            return redirect()->route('ticketing.seats', $schedule)
                ->with('error', 'Seat is no longer available.');
        }

        $travelDate = $request->input('travel_date', now()->format('Y-m-d'));
        return view('ticketing.booking', compact('schedule', 'seat', 'travelDate'));
    }

    /**
     * Process booking.
     */
    public function processBooking(Request $request)
    {
        // Check if it's a multiple seat booking
        if ($request->has('is_multiple')) {
            return $this->processMultipleBooking($request);
        }

        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'seat_id' => 'required|exists:seats,id',
            'passenger_name' => 'required|string|max:255',
            'passenger_phone' => 'required|string|max:20',
            'travel_date' => 'required|date',
        ]);

        return DB::transaction(function () use ($request) {
            $user = Auth::user();
            $schedule = Schedule::findOrFail($request->schedule_id);
            $seat = Seat::findOrFail($request->seat_id);

            // Check if seat is still available
            if (!$seat->is_available) {
                return back()->with('error', 'Seat is no longer available.');
            }

            // Generate unique booking code
            $bookingCode = 'BTB-' . strtoupper(uniqid());

            // Create booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'booking_code' => $bookingCode,
                'travel_date' => $request->travel_date,
                'seat_count' => 1,
                'seat_numbers' => [$seat->seat_number],
                'passenger_details' => [[
                    'name' => $request->passenger_name,
                    'phone' => $request->passenger_phone,
                    'seat_number' => $seat->seat_number
                ]],
                'total_amount' => $schedule->price,
                'status' => 'pending',
                'booking_date' => now(),
            ]);

            // Update seat status and link to booking
            $seat->update([
                'status' => 'booked',
                'booking_id' => $booking->id
            ]);
            $schedule->decrement('available_seats');

            return redirect()->route('ticketing.booking-success', $booking)
                ->with('success', 'Booking created successfully!');
        });
    }

    /**
     * Process multiple seat booking.
     */
    private function processMultipleBooking(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'seat_ids' => 'required|string',
            'travel_date' => 'required|date',
            'passengers' => 'required|array|min:1',
            'passengers.*.name' => 'required|string|max:255',
            'passengers.*.phone' => 'required|string|max:20',
            'passengers.*.seat_id' => 'required|exists:seats,id',
        ]);

        return DB::transaction(function () use ($request) {
            $user = Auth::user();
            $schedule = Schedule::findOrFail($request->schedule_id);
            $seatIds = explode(',', $request->seat_ids);
            $seats = Seat::whereIn('id', $seatIds)->get();
            
            // Verify all seats are still available
            foreach ($seats as $seat) {
                if (!$seat->is_available) {
                    return back()->with('error', "Seat {$seat->seat_number} is no longer available.");
                }
            }

            // Generate unique booking code
            $bookingCode = 'BTB-' . strtoupper(uniqid());
            $totalPrice = $schedule->price * count($seatIds);

            // Prepare passenger details
            $passengerDetails = [];
            foreach ($request->passengers as $passenger) {
                $passengerDetails[] = [
                    'name' => $passenger['name'],
                    'phone' => $passenger['phone'],
                    'seat_number' => $passenger['seat_number']
                ];
            }

            // Create booking with multiple seats
            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'travel_date' => $request->travel_date,
                'seat_count' => count($seatIds),
                'seat_numbers' => $seats->pluck('seat_number')->toArray(),
                'passenger_details' => $passengerDetails,
                'total_amount' => $totalPrice,
                'status' => 'pending',
                'booking_date' => now(),
            ]);

            // Update seat statuses and booking IDs
            foreach ($seats as $seat) {
                $seat->update([
                    'status' => 'booked',
                    'booking_id' => $booking->id
                ]);
            }

            // Update schedule available seats
            $schedule->decrement('available_seats', count($seatIds));

            return redirect()->route('ticketing.booking-success', $booking)
                ->with('success', 'Multiple seat booking created successfully!');
        });
    }

    /**
     * Show booking success page.
     */
    public function bookingSuccess(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $booking->load(['schedule.route']);
        
        return view('ticketing.booking-success', compact('booking'));
    }

    /**
     * Show user's bookings.
     */
    public function myBookings()
    {
        $bookings = Booking::with(['schedule.route', 'payment'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('ticketing.my-bookings', compact('bookings'));
    }

    /**
     * Show booking form for multiple seats.
     */
    public function bookingMultiple(Schedule $schedule, Request $request)
    {
        $seatIds = explode(',', $request->input('seats', ''));
        $seats = Seat::whereIn('id', $seatIds)->get();
        $travelDate = $request->input('travel_date', now()->format('Y-m-d'));
        
        // Verify all seats are available and belong to the schedule
        foreach($seats as $seat) {
            if (!$seat->is_available || $seat->schedule_id != $schedule->id) {
                return redirect()->route('ticketing.seats', $schedule)
                    ->with('error', 'One or more seats are no longer available.');
            }
        }

        return view('ticketing.booking-multiple', compact('schedule', 'seats', 'travelDate'));
    }
}
