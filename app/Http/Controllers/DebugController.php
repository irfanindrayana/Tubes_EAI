<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function checkSchedule($id)
    {
        $schedule = Schedule::with('route', 'bookings')->find($id);
        
        if (!$schedule) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }
        
        // Basic info
        $result = [
            'id' => $schedule->id,
            'route_id' => $schedule->route_id,
            'departure_time' => $schedule->departure_time,
            'arrival_time' => $schedule->arrival_time,
            'total_seats' => $schedule->total_seats,
            'available_seats' => $schedule->available_seats,
            'bus_code' => $schedule->bus_code,
            'price' => $schedule->price,
            'is_active' => $schedule->is_active ? "Yes (1)" : "No (0)",
            'days_of_week' => json_encode($schedule->days_of_week),
            'bookings_count' => $schedule->bookings->count(),
            'current_day_of_week' => date('w'), // 0 (Sunday) to 6 (Saturday)
            'days_of_week_raw' => $schedule->getOriginal('days_of_week'),
        ];
        
        // Check if today is in operating days
        $dayOfWeek = date('w');
        $operatesToday = false;
          if (is_array($schedule->days_of_week)) {
            $operatesToday = in_array((string)$dayOfWeek, $schedule->days_of_week) || in_array((int)$dayOfWeek, $schedule->days_of_week);
        } elseif (is_string($schedule->days_of_week)) {
            $days = json_decode($schedule->days_of_week, true);
            $operatesToday = is_array($days) && (in_array((string)$dayOfWeek, $days) || in_array((int)$dayOfWeek, $days));
        }
        
        $result['operates_today'] = $operatesToday ? "Yes" : "No";
        
        // Add route details
        if ($schedule->route) {
            $result['route'] = [
                'origin' => $schedule->route->origin,
                'destination' => $schedule->route->destination,
                'is_active' => $schedule->route->is_active ? "Yes (1)" : "No (0)",
            ];
        }
        
        // Add availability determination
        $result['why_unavailable'] = $this->determineAvailabilityReason($schedule);
        
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $result['available_days'] = [];
        
        if (is_array($schedule->days_of_week)) {
            foreach ($schedule->days_of_week as $day) {
                if (isset($days[(int)$day])) {
                    $result['available_days'][] = $days[(int)$day] . " (Day $day)";
                }
            }
        }
        
        // Recommended fix
        $result['recommended_fix'] = $this->getRecommendedFix($schedule);
        
        return response()->json($result);
    }
    
    private function determineAvailabilityReason($schedule)
    {
        $reasons = [];
        
        if (!$schedule->is_active) {
            $reasons[] = "Schedule is inactive (is_active = 0)";
        }
        
        $dayOfWeek = date('w');
        $operatesToday = false;
        
        if (is_array($schedule->days_of_week)) {
            if (!in_array((string)$dayOfWeek, $schedule->days_of_week) && !in_array($dayOfWeek, $schedule->days_of_week)) {
                $operatesToday = false;
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $reasons[] = "Schedule doesn't operate on " . $days[$dayOfWeek] . " (current day $dayOfWeek)";
            } else {
                $operatesToday = true;
            }
        } elseif (is_string($schedule->days_of_week)) {
            try {
                $days = json_decode($schedule->days_of_week, true);
                if (!is_array($days) || (!in_array((string)$dayOfWeek, $days) && !in_array($dayOfWeek, $days))) {
                    $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    $reasons[] = "Schedule doesn't operate on " . $daysOfWeek[$dayOfWeek] . " (day $dayOfWeek)";
                } else {
                    $operatesToday = true;
                }
            } catch (\Exception $e) {
                $reasons[] = "Error parsing days_of_week JSON: " . $e->getMessage();
            }
        } else {
            $reasons[] = "days_of_week is neither array nor string";
        }
        
        if ($schedule->available_seats <= 0) {
            $reasons[] = "No available seats (available_seats = " . $schedule->available_seats . ")";
        }
        
        return empty($reasons) ? "Schedule should be available" : implode(", ", $reasons);
    }
    
    private function getRecommendedFix($schedule)
    {
        if (!$schedule->is_active) {
            return "Set schedule to active using admin toggle button";
        }
        
        $dayOfWeek = date('w');
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        if (is_array($schedule->days_of_week)) {
            if (!in_array((string)$dayOfWeek, $schedule->days_of_week) && !in_array($dayOfWeek, $schedule->days_of_week)) {
                return "Add " . $days[$dayOfWeek] . " (day $dayOfWeek) to schedule operating days";
            }
        } elseif (is_string($schedule->days_of_week)) {
            try {
                $daysArray = json_decode($schedule->days_of_week, true);
                if (!is_array($daysArray) || (!in_array((string)$dayOfWeek, $daysArray) && !in_array($dayOfWeek, $daysArray))) {
                    return "Add " . $days[$dayOfWeek] . " (day $dayOfWeek) to schedule operating days";
                }
            } catch (\Exception $e) {
                return "Fix days_of_week JSON format";
            }
        }
        
        if ($schedule->available_seats <= 0) {
            return "Increase available seats (currently " . $schedule->available_seats . ")";
        }
        
        return "Schedule seems valid - check for other issues";
    }
}
