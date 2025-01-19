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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain');
            $table->enum('mode',['company','saas'])->default('saas');
            $table->json('settings')->nullable();
            $table->enum('status', CRM_STATUS_TYPES['TENANTS']['TABLE_STATUS']);
            $table->string('currency_code')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
