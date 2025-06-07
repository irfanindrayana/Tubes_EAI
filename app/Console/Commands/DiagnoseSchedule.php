<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use Carbon\Carbon;

class DiagnoseSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:schedule {id : The ID of the schedule to diagnose}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose a schedule to determine why it might be unavailable';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        $this->info("Diagnosing Schedule ID: $id");
        $this->newLine();
        
        $schedule = Schedule::with('route', 'bookings')->find($id);
        
        if (!$schedule) {
            $this->error("Schedule with ID $id not found!");
            return 1;
        }
        
        $this->info("SCHEDULE DETAILS");
        $this->info("---------------");
        $this->line("<fg=yellow>Route:</> {$schedule->route->origin} → {$schedule->route->destination}");
        $this->line("<fg=yellow>Departure:</> " . Carbon::parse($schedule->departure_time)->format('Y-m-d H:i'));
        $this->line("<fg=yellow>Bus Code:</> " . ($schedule->bus_code ?? 'BUS-' . $schedule->id));
        $this->line("<fg=yellow>Total Seats:</> {$schedule->total_seats}");
        $this->line("<fg=yellow>Available Seats:</> {$schedule->available_seats}");
        $this->newLine();
        
        $this->info("STATUS ANALYSIS");
        $this->info("---------------");
        
        // Check if schedule is active
        if (!$schedule->is_active) {
            $this->error("❌ Schedule is INACTIVE");
            $this->line("   <fg=green>Fix:</> Run 'php artisan schedule:activate $id'");
        } else {
            $this->line("✅ <fg=green>Schedule is active</>");
        }
          // Check operating dates
        $today = date('Y-m-d');
        $this->line("<fg=yellow>Today is:</> $today");
        
        // Check if schedule operates today via specific dates
        $operatesToday = $schedule->operatesOnDate($today);
        $scheduleDatesCount = $schedule->scheduleDates()->count();
        
        $this->line("<fg=yellow>Total scheduled dates:</> $scheduleDatesCount");
          if (!$operatesToday) {
            $this->error("❌ Schedule does NOT operate today ($today)");
            $this->line("   <fg=green>Fix:</> Add specific dates to the schedule");
        } else {
            $this->line("✅ <fg=green>Schedule operates today</>");
        }
        
        // Check if schedule has available seats
        if ($schedule->available_seats <= 0) {
            $this->error("❌ Schedule has NO available seats");
            $this->line("   <fg=green>Fix:</> Run 'php artisan schedule:set-seats $id [number]'");
        } else {
            $this->line("✅ <fg=green>Schedule has {$schedule->available_seats} available seats</>");
        }
        
        // Overall availability
        $this->newLine();
        $this->info("AVAILABILITY CONCLUSION");
        $this->info("----------------------");
        
        $isAvailable = $schedule->is_active && $operatesToday && $schedule->available_seats > 0;
        if ($isAvailable) {
            $this->line("✅ <fg=green>Schedule should be available</>");
            $this->line("   If still showing as unavailable, check other conditions in the code.");
        } else {
            $this->error("❌ Schedule is UNAVAILABLE because:");
            if (!$schedule->is_active) {
                $this->line("   - It is inactive");
            }
            if (!$operatesToday) {
                $this->line("   - It doesn't operate on $dayNames[$currentDay]");
            }
            if ($schedule->available_seats <= 0) {
                $this->line("   - It has no available seats");
            }
        }
        
        return 0;
    }
}
