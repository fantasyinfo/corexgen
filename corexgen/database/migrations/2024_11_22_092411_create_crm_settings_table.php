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
        Schema::create('crm_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Ensures unique settings keys
            $table->text('value')->nullable(); // Allows larger values
            $table->boolean('is_media_setting')->default(false); // Flags for media
            $table->unsignedBigInteger('media_id')->nullable(); // Foreign key for media
            $table->boolean('is_tenant')->default(false); // Tenant-specific setting
            $table->unsignedBigInteger('company_id')->nullable(); // SaaS company association
            $table->string('input_type')->nullable(); // Input type for rendering UI
            $table->unsignedBigInteger('updated_by')->nullable(); // Tracking who updated
            $table->unsignedBigInteger('created_by')->nullable(); // Tracking who created
            $table->timestamps();
    
            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            // index 
            $table->index(['key', 'company_id', 'is_tenant']);
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_settings');
    }
};
