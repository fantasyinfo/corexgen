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
        Schema::create('crm_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_media_setting')->default(false);
            $table->unsignedBigInteger('media_id')->nullable();
            $table->boolean('is_tenant')->default(false);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('input_type')->nullable();
            $table->string('type')->nullable();
            $table->string('value_type');
            $table->string('placeholder')->nullable();
            $table->string('name')->unique();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('set null');
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
