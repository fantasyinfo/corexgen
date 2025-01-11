<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();

            // Event Details
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type')->nullable(); // meeting, task, appointment, etc.
            $table->string('priority')->default('medium'); // high, medium, low
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('location')->nullable();
            $table->text('meeting_link')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('color')->nullable();
            $table->json('tags')->nullable();
            
            // Status Management
            $table->enum('status', [
                'upcoming',
                'in_progress',
                'completed',
                'canceled',
                'postponed'
            ])->default('upcoming');

            // Privacy & Access Control
            $table->boolean('is_private')->default(false);
            $table->json('attachments')->nullable();

            // Event Ownership & Attendees
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->json('attendees')->nullable();

            // Recurring Event Settings
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable(); // daily, weekly, monthly
            $table->integer('recurrence_interval')->nullable();
            $table->dateTime('recurrence_end_date')->nullable();

            // Notifications and Reminders
            $table->boolean('send_notifications')->default(true);
            $table->json('notification_settings')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes for Performance
            $table->index(['company_id', 'start_date']);
            $table->index(['created_by', 'start_date']);
            $table->index(['status', 'start_date']);
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};