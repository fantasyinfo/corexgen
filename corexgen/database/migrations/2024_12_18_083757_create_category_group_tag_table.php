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
        Schema::create('category_group_tag', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#03D1AB');

            $table->enum('relation_type', CATEGORY_GROUP_TAGS_RELATIONS['TABLE_STATUS'])->default(CATEGORY_GROUP_TAGS_RELATIONS['STATUS']['clients']);

            $table->enum('type', CATEGORY_GROUP_TAGS_TYPES['TABLE_STATUS'])->default(CATEGORY_GROUP_TAGS_TYPES['STATUS']['categories']);

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->timestamps();

            // Add unique constraint for name, type, and company_id
            $table->unique(['name', 'type', 'relation_type', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_group_tag');
    }
};
