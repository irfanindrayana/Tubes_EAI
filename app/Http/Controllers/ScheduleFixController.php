<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;

class ScheduleFixController extends Controller
{
    /**
     * Display a form to help fix schedule issues.
     */
    public function showFixForm(Schedule $schedule)
    {
        // Get day names and current day of week
        $days = [
            0 => 'Sunday',
            1 => 'Monday', 
            2 => 'Tuesday', 
            3 => 'Wednesday', 
            4 => 'Thursday', 
            5 => 'Friday', 
            6 => 'Saturday'
        ];
        
        $currentDay = date('w');
        
        // Convert days_of_week to consistent format
        $selectedDays = [];
        if (is_array($schedule->days_of_week)) {
            $selectedDays = $schedule->days_of_week;
        } elseif (is_string($schedule->days_of_week)) {
            try {
                $decoded = json_decode($schedule->days_of_week, true);
                if (is_array($decoded)) {
                    $selectedDays = $decoded;
                }
            } catch (\Exception $e) {
                // Handle invalid JSON
            }
        }
        
        return view('admin.schedules.fix', compact('schedule', 'days', 'currentDay', 'selectedDays'));
    }
    
    /**
     * Apply fixes to schedule.
     */
    public function applyFixes(Request $request, Schedule $schedule)
    {
        // Validate request
        $request->validate([
            'is_active' => 'boolean',
            'days_of_week' => 'array',
            'available_seats' => 'integer|min:0|max:' . $schedule->total_seats,
        ]);
        
        // Update schedule
        $data = [];
        
        if ($request->has('is_active')) {
            $data['is_active'] = (bool) $request->is_active;
        }
        
        if ($request->has('days_of_week')) {
            $data['days_of_week'] = json_encode($request->days_of_week);
        }
        
        if ($request->has('available_seats')) {
            $data['available_seats'] = $request->available_seats;
        }
        
        $schedule->update($data);
        
        return redirect()->back()->with('success', 'Schedule fixed successfully!');
    }
}
