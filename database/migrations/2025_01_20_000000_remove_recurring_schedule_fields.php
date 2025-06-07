<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'ticketing';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, convert all existing recurring schedules to specific dates
        $this->convertRecurringToSpecificDates();
        
        // Remove unnecessary fields from schedules table
        Schema::connection('ticketing')->table('schedules', function (Blueprint $table) {
            if (Schema::connection('ticketing')->hasColumn('schedules', 'days_of_week')) {
                $table->dropColumn('days_of_week');
            }
            
            if (Schema::connection('ticketing')->hasColumn('schedules', 'use_specific_dates')) {
                $table->dropColumn('use_specific_dates');
            }
            
            if (Schema::connection('ticketing')->hasColumn('schedules', 'travel_date')) {
                $table->dropColumn('travel_date');
            }
        });
    }

    /**
     * Convert existing recurring schedules to specific dates for the next 90 days
     */
    private function convertRecurringToSpecificDates()
    {
        $schedules = DB::connection('ticketing')->table('schedules')
            ->where('use_specific_dates', false)
            ->orWhereNull('use_specific_dates')
            ->get();        foreach ($schedules as $schedule) {
            $daysOfWeek = [];
            
            // Handle different data types for days_of_week
            if (is_string($schedule->days_of_week)) {
                try {
                    $daysOfWeek = json_decode($schedule->days_of_week, true) ?? [];
                } catch (\Exception $e) {
                    $daysOfWeek = [];
                }
            } elseif (is_array($schedule->days_of_week)) {
                $daysOfWeek = $schedule->days_of_week;
            }
            
            // Ensure $daysOfWeek is an array
            if (!is_array($daysOfWeek)) {
                $daysOfWeek = [];
            }
            
            if (!empty($daysOfWeek)) {
                // Generate dates for the next 90 days based on days_of_week
                $startDate = now();
                $endDate = now()->addDays(90);
                
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dayOfWeek = $date->dayOfWeek; // 0=Sunday, 6=Saturday
                    
                    if (in_array($dayOfWeek, $daysOfWeek) || in_array((string)$dayOfWeek, $daysOfWeek)) {
                        // Check if this date already exists
                        $exists = DB::connection('ticketing')->table('schedule_dates')
                            ->where('schedule_id', $schedule->id)
                            ->where('scheduled_date', $date->format('Y-m-d'))
                            ->exists();
                            
                        if (!$exists) {
                            DB::connection('ticketing')->table('schedule_dates')->insert([
                                'schedule_id' => $schedule->id,
                                'scheduled_date' => $date->format('Y-m-d'),
                                'is_active' => true,
                                'notes' => 'Converted from recurring schedule',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
                
                // Update the schedule to use specific dates
                DB::connection('ticketing')->table('schedules')
                    ->where('id', $schedule->id)
                    ->update(['use_specific_dates' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('ticketing')->table('schedules', function (Blueprint $table) {
            $table->json('days_of_week')->nullable()->after('available_seats');
            $table->boolean('use_specific_dates')->default(false)->after('days_of_week');
            $table->date('travel_date')->nullable()->after('arrival_time');
        });
    }
};
