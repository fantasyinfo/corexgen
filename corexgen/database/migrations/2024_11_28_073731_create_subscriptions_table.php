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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('payment_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_billing_date');
            $table->enum('billing_cycle', PLANS_BILLING_CYCLES['BILLINGS_TABLE'])->default(PLANS_BILLING_CYCLES['BILLINGS']['1 MONTH']);
            $table->unsignedBigInteger('pervious_plan_id')->nullable();
            $table->date('upgrade_date')->nullable();
            $table->enum('status', CRM_STATUS_TYPES['SUBSCRIPTION']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['SUBSCRIPTION']['STATUS']['ACTIVE']);

            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payment_transactions')->onDelete('cascade');
            $table->foreign('pervious_plan_id')->references('id')->on('plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
