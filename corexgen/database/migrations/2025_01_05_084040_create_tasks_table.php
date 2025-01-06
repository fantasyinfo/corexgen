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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
      
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('billable')->default(false);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->morphs('relatable');


            $table->boolean('visible_to_client')->default(false);
       
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('milestone_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('company_id');

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('milestone_id')->references('id')->on('milestones')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('category_group_tag')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
            
            $table->unique(['task_id', 'user_id', 'company_id']);
            
            // Indexes for the pivot table
            $table->index('task_id');
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
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_user');
    }
};
