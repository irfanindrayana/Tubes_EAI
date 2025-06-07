<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    protected $connection = 'ticketing';

    public function up(): void
    {
        Schema::connection('ticketing')->table('routes', function (Blueprint $table) {
            if (!Schema::connection('ticketing')->hasColumn('routes', 'estimated_duration')) {
                $table->integer('estimated_duration')->nullable()->comment('Estimated duration in minutes')->after('distance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('ticketing')->table('routes', function (Blueprint $table) {
            if (Schema::connection('ticketing')->hasColumn('routes', 'estimated_duration')) {
                $table->dropColumn('estimated_duration');
            }
        });
    }
};
