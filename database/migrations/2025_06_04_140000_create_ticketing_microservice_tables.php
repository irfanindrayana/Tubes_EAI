<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'ticketing';    /**
     * Run the migrations.
     */    public function up(): void
    {        // Routes table
        Schema::connection('ticketing')->create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('origin');
            $table->string('destination');
            $table->json('stops')->nullable();
            $table->decimal('distance', 8, 2)->nullable();
            $table->integer('estimated_duration')->nullable()->comment('Estimated duration in minutes');
            $table->decimal('base_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });        
        
        // Schedules table
        Schema::connection('ticketing')->create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->onDelete('cascade');
            $table->string('bus_code');
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->integer('total_seats')->default(40);
            $table->integer('available_seats')->default(40);
            $table->json('days_of_week')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });        
        
        // Seats table (without foreign key to bookings for now)
        Schema::connection('ticketing')->create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->string('seat_number');
            $table->date('travel_date');
            $table->enum('status', ['available', 'booked', 'reserved'])->default('available');
            $table->unsignedBigInteger('booking_id')->nullable(); // No foreign key constraint yet
            $table->timestamps();
            
            $table->unique(['schedule_id', 'seat_number', 'travel_date']);
        });
        
        // Bookings table (no foreign key to users - handled by application logic)
        Schema::connection('ticketing')->create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->unsignedBigInteger('user_id'); // No foreign key constraint
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->date('travel_date');
            $table->integer('seat_count');
            $table->json('seat_numbers');
            $table->json('passenger_details');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->timestamp('booking_date')->nullable();
            $table->timestamps();
        });
        
        // Now add the foreign key constraint to seats table
        Schema::connection('ticketing')->table('seats', function (Blueprint $table) {
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
        });
    }    /**
     * Reverse the migrations.
     */    public function down(): void
    {
        Schema::connection('ticketing')->dropIfExists('seats');
        Schema::connection('ticketing')->dropIfExists('bookings');
        Schema::connection('ticketing')->dropIfExists('schedules');
        Schema::connection('ticketing')->dropIfExists('routes');
    }
};
