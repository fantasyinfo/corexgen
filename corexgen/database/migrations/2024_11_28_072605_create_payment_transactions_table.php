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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('company_id');
            $table->decimal('amount');
            $table->string('currency')->default('USD');
            $table->string('payment_gateway')->nullable();
            $table->enum('payment_type', ['ONLINE', 'OFFLINE'])->default('ONLINE');
            $table->json('transaction_refrence')->nullable();
            $table->enum('status', CRM_STATUS_TYPES['PAYMENTSTRANSACTIONS']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['PAYMENTSTRANSACTIONS']['STATUS']['PENDING']);

            $table->dateTime('transaction_date')->default(now());

            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
