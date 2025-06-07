<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;

class ScheduleSetSeats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:set-seats {id : The ID of the schedule} {seats : Number of available seats to set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the number of available seats for a schedule';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        $seats = $this->argument('seats');
        
        if (!is_numeric($seats) || $seats < 0) {
            $this->error("Invalid number of seats. Must be a non-negative number.");
            return 1;
        }
        
        $this->info("Setting available seats to $seats for Schedule ID: $id");
        
        $schedule = Schedule::find($id);
        
        if (!$schedule) {
            $this->error("Schedule with ID $id not found!");
            return 1;
        }
        
        if ($seats > $schedule->total_seats) {
            $this->error("Available seats cannot exceed total seats ({$schedule->total_seats}).");
            return 1;
        }
        
        // Update schedule
        $schedule->update(['available_seats' => $seats]);
        
        $this->info("✅ Available seats set to $seats successfully!");
        
        return 0;
    }
}
