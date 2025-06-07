<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;

class ScheduleActivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:activate {id : The ID of the schedule to activate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activates an inactive schedule';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        $this->info("Activating Schedule ID: $id");
        
        $schedule = Schedule::find($id);
        
        if (!$schedule) {
            $this->error("Schedule with ID $id not found!");
            return 1;
        }
        
        if ($schedule->is_active) {
            $this->line("Schedule is already active.");
            return 0;
        }
        
        $schedule->update(['is_active' => true]);
        $this->info("✅ Schedule activated successfully!");
        
        return 0;
    }
}
