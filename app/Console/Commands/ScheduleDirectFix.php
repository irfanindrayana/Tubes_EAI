<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScheduleDirectFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:direct-fix {id : The ID of the schedule to fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Directly fix a schedule with SQL update';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        $this->info("Directly fixing Schedule ID: $id");
        
        // Get current day of week (0-6)
        $currentDay = date('w');
        
        // Update with direct SQL to ensure proper JSON formatting
        $json = json_encode(array_map('strval', [0, 1, 2, 3, 4, 5, 6]));
        
        // Using DB facade to update directly
        $affected = DB::connection('ticketing')
            ->table('schedules')
            ->where('id', $id)
            ->update([
                'is_active' => 1,
                'days_of_week' => $json,
                'available_seats' => DB::raw('total_seats')
            ]);
            
        if ($affected) {
            $this->info("✅ Schedule fixed successfully!");
            $this->line("- Set to active");
            $this->line("- Added all days of the week");
            $this->line("- Reset available seats to total seats");
        } else {
            $this->error("Failed to update schedule");
        }
        
        return $affected ? 0 : 1;
    }
}
