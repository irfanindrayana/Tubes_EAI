<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'inbox';

    /**
     * Run the migrations.
     */
    public function up(): void
    {        // Messages table
        Schema::connection('inbox')->create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_code')->unique();
            $table->unsignedBigInteger('sender_id'); // Reference to user in user management service
            $table->string('subject');
            $table->text('content');
            $table->enum('type', ['notification', 'promotion', 'announcement', 'payment_verification', 'system', 'personal', 'support']);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->json('attachments')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });        // Message recipients table
        Schema::connection('inbox')->create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
            $table->unsignedBigInteger('recipient_id'); // Reference to user in user management service
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            
            $table->unique(['message_id', 'recipient_id']);
        });        // Notifications table
        Schema::connection('inbox')->create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_code')->unique();
            $table->unsignedBigInteger('user_id'); // Reference to user in user management service
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['booking', 'payment', 'promotion', 'system', 'info']);
            $table->json('data')->nullable(); // Additional data
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('inbox')->dropIfExists('notifications');
        Schema::connection('inbox')->dropIfExists('message_recipients');
        Schema::connection('inbox')->dropIfExists('messages');
    }
};
