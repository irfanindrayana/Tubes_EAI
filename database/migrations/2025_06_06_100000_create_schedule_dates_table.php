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
        // Create schedule_dates table for specific date scheduling
        Schema::connection('ticketing')->create('schedule_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->date('scheduled_date');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Prevent duplicate dates for same schedule
            $table->unique(['schedule_id', 'scheduled_date']);
            
            // Index for fast lookups
            $table->index(['scheduled_date', 'is_active']);
        });
        
        // Update schedules table to mark it as date-based scheduling
        Schema::connection('ticketing')->table('schedules', function (Blueprint $table) {
            $table->boolean('use_specific_dates')->default(false)->after('days_of_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('ticketing')->table('schedules', function (Blueprint $table) {
            $table->dropColumn('use_specific_dates');
        });
        
        Schema::connection('ticketing')->dropIfExists('schedule_dates');
    }
};
