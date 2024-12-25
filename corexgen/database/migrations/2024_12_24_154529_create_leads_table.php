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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Individual', 'Company']);
            $table->string('company_name')->nullable();
            $table->string('title');
            $table->decimal('value', 15, 2)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->text('details')->nullable();
            $table->dateTime('last_contacted_date')->nullable();
            $table->dateTime('last_activity_date')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium');
            $table->enum('preferred_contact_method', ['Email', 'Phone', 'In-Person'])->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->dateTime('follow_up_date')->nullable();
            $table->boolean('is_converted')->default(false);

            $table->enum('status', CRM_STATUS_TYPES['LEADS']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['LEADS']['STATUS']['ACTIVE']);
            
            // foreign ids
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assign_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('group_id')->nullable()->constrained('category_group_tag')->onDelete('set null');
            $table->foreignId('source_id')->nullable()->constrained('category_group_tag')->onDelete('set null');
            $table->foreignId('status_id')->nullable()->constrained('category_group_tag')->onDelete('set null');
            $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');

            $table->timestamps();

            // Indexes for frequently searched/filtered fields
            $table->index('type');
            $table->index('priority');
            $table->index('status');
            $table->index('is_converted');
            $table->index('score');
            $table->index('company_id');
            $table->index('last_activity_date');
            $table->index('follow_up_date');
            $table->index('last_contacted_date');
            
            // Compound indexes for common query combinations
            $table->index(['company_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['priority', 'status']);
            $table->index(['company_id', 'is_converted']);
            $table->index(['last_activity_date', 'status']);
            $table->index(['follow_up_date', 'status']);
        });

        // Pivot table for multiple assignees
        Schema::create('lead_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['lead_id', 'user_id', 'company_id']);
            
            // Indexes for the pivot table
            $table->index('lead_id');
            $table->index('user_id');
            $table->index('company_id');
            $table->index(['company_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
        Schema::dropIfExists('lead_user');
    }
};