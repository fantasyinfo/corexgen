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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Tax');
            $table->decimal('tax_rate')->default(18.0);
            $table->string('tax_type')->nullable()->default('GST');
            $table->enum('status', CRM_STATUS_TYPES['TAX_RATES']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['TAX_RATES']['STATUS']['ACTIVE']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
