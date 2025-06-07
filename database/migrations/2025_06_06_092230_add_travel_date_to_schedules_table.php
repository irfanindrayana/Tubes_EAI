<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'ticketing';
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('ticketing')->table('schedules', function (Blueprint $table) {
            // Add travel_date column for future use (optional)
            // This would allow specific date-based schedules instead of just recurring weekly schedules
            $table->date('travel_date')->nullable()->after('arrival_time')->comment('Specific travel date for this schedule instance');
            
            // Add index for better performance
            $table->index(['travel_date', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('ticketing')->table('schedules', function (Blueprint $table) {
            $table->dropIndex(['travel_date', 'is_active']);
            $table->dropColumn('travel_date');
        });
    }
};
