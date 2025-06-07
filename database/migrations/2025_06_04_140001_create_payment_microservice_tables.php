<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'payment';

    /**
     * Run the migrations.
     */
    public function up(): void
    {        // Payment methods table
        Schema::connection('payment')->create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->json('configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('fee_percentage', 5, 2)->default(0);
            $table->decimal('fee_fixed', 10, 2)->default(0);
            $table->timestamps();
        });// Payments table (no foreign keys to other microservices)
        Schema::connection('payment')->create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_code')->unique();
            $table->unsignedBigInteger('booking_id'); // Reference to booking in ticketing service
            $table->unsignedBigInteger('user_id'); // Reference to user in user management service
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('proof_image')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('payment')->dropIfExists('payments');
        Schema::connection('payment')->dropIfExists('payment_methods');
    }
};
