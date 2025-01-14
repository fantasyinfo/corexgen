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
        Schema::create('web_to_leads_form', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->string('title');


            $table->foreignId('group_id')->nullable()->constrained('category_group_tag')->onDelete('set null');
            $table->foreignId('source_id')->nullable()->constrained('category_group_tag')->onDelete('set null');
            $table->foreignId('status_id')->nullable()->constrained('category_group_tag')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');

            $table->unique(['title','company_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_to_leads_form');
    }
};
