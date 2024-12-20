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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Individual', 'Company'])->default('Individual');
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->json('email')->nullable(); // Multiple emails
            $table->json('phone')->nullable(); // Multiple phone numbers
            $table->json('social_media')->nullable(); // Social media links
            $table->enum('category', CLIENTS_CATEGORY_TYPES['TABLE_STATUS'])->nullable();
            $table->text('details')->nullable(); // WYSIWYG editor content
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->enum('status', CRM_STATUS_TYPES['CLIENTS']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['CLIENTS']['STATUS']['ACTIVE']);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('tags')->nullable(); // Optional tagging system
            $table->date('birthdate')->nullable(); // Optional for individuals
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
