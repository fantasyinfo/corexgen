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
        Schema::create('company_onboarding', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('address')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('timezone')->nullable();
            $table->string('plan_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->boolean('payment_completed')->default(false);
            $table->enum('status', CRM_STATUS_TYPES['COMPANIES_ONBORDING']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['COMPANIES_ONBORDING']['STATUS']['NOT_STARTED']);

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_onboarding');
    }
};
