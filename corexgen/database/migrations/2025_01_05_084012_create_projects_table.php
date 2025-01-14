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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('billing_type', ['Hourly', 'One-Time']);
            $table->integer('one_time_cost')->nullable();
            $table->integer('per_hour_cost')->nullable();
            $table->integer('progress')->default(0);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('deadline')->nullable();

            $table->integer('estimated_hours')->nullable();
            $table->integer('time_spent')->default(0);



            $table->enum('status', CRM_STATUS_TYPES['PROJECTS']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['PROJECTS']['STATUS']['ACTIVE']);

            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('company_id');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
            
            $table->unique(['project_id', 'user_id', 'company_id']);
            
            // Indexes for the pivot table
            $table->index('project_id');
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
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_user');
    }
};
