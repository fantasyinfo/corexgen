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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('desc')->nullable()->default('for startups');
            $table->integer('users_limit');
            $table->integer('roles_limit');
            $table->decimal('price');
            $table->decimal('offer_price');
            $table->enum('billing_cycle', PLANS_BILLING_CYCLES['BILLINGS_TABLE']);
            $table->enum('status', CRM_STATUS_TYPES['PLANS']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['PLANS']['STATUS']['ACTIVE']);

            $table->unsignedBigInteger('tax_rates_id')->nullable();
            $table->foreign('tax_rates_id')->references('id')->on('tax_rates')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
