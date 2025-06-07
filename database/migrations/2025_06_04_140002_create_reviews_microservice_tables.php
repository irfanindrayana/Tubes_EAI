<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'reviews';

    /**
     * Run the migrations.
     */
    public function up(): void
    {        // Reviews table
        Schema::connection('reviews')->create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Reference to user in user management service
            $table->unsignedBigInteger('booking_id'); // Reference to booking in ticketing service
            $table->unsignedBigInteger('route_id'); // Reference to route in ticketing service
            $table->integer('rating'); // 1-5 scale
            $table->text('comment')->nullable();
            $table->json('aspects_rating')->nullable();
            $table->enum('status', ['active', 'hidden', 'flagged'])->default('active');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });        // Complaints table
        Schema::connection('reviews')->create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code')->unique();
            $table->unsignedBigInteger('user_id'); // Reference to user in user management service
            $table->unsignedBigInteger('booking_id')->nullable(); // Reference to booking in ticketing service
            $table->string('subject');
            $table->text('description');
            $table->enum('category', ['service', 'driver', 'vehicle', 'booking', 'payment', 'technical', 'other']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable(); // Reference to admin user
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('reviews')->dropIfExists('complaints');
        Schema::connection('reviews')->dropIfExists('reviews');
    }
};
