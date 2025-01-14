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
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('config_key'); // 
            $table->text('config_value'); // 
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->index();
            $table->enum('mode', ['TEST', 'LIVE'])->default('TEST')->index();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways')->onDelete('set null');

            $table->unique(['config_key', 'company_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_settings');
    }
};
