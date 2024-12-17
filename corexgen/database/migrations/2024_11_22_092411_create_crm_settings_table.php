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
            $table->string('key');
            $table->text('value')->nullable();
            $table->boolean('is_media_setting')->default(false);
            $table->unsignedBigInteger('media_id')->nullable();
            $table->boolean('is_tenant')->default(false);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('input_type')->nullable();
            $table->string('type')->nullable();
            $table->string('value_type');
            $table->string('placeholder')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            // Index
            $table->index(['key', 'company_id', 'is_tenant']);

            // Unique constraints for composite columns
            $table->unique(['key', 'company_id'], 'unique_key_company');   // Unique for key + company_id
            $table->unique(['name', 'company_id'], 'unique_name_company'); // Unique for name + company_id
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
