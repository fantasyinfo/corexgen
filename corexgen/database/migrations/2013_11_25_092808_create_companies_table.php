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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->string('email')->unique()->index();
            $table->string('phone')->nullable();
            $table->enum('status', CRM_STATUS_TYPES['COMPANIES']['TABLE_STATUS']);


            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->unsignedBigInteger('plan_id');

            $table->string('api_token', 255)->nullable()->index();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
