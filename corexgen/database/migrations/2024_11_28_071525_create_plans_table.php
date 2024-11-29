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
       
            $table->decimal('price');
            $table->decimal('offer_price');
            $table->enum('billing_cycle', PLANS_BILLING_CYCLES['BILLINGS_TABLE']);
            $table->enum('status', CRM_STATUS_TYPES['PLANS']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['PLANS']['STATUS']['ACTIVE']);


            // features
            $table->integer('users_limit')->default(10);
            $table->integer('roles_limit')->default(10);


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
