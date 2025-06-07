<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
    }
};
