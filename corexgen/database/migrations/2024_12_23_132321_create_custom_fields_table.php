<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // Migration file for custom_field_definitions and custom_field_values tables

    public function up()
    {
        // Create custom_field_definitions table
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();

            $table->string('entity_type', 50); // 'user', 'client', 'role', etc.
            $table->string('field_name', 100);
            $table->string('field_type', 20); // 'text', 'number', 'date', 'select', etc.
            $table->string('field_label', 100);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('options')->nullable(); // For dropdown/multi-select options
            $table->json('validation_rules')->nullable(); // For field validation
            $table->timestamps();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');


            // Indexes and unique keys
            $table->index(['company_id', 'entity_type'], 'idx_company_entity');
            $table->unique(['company_id', 'entity_type', 'field_name'], 'uk_company_entity_field');
        });

        // Create custom_field_values table
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('entity_id'); // ID of user/client/role
            $table->text('field_value')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['definition_id', 'entity_id'], 'idx_definition_entity');

            // Foreign key constraints
            $table->foreignId('definition_id')->nullable()->constrained('custom_field_definitions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_field_definitions');
    }
};
