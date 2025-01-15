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
        Schema::create('payment_gateway_store_session', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('config_key'); // 
            $table->text('config_value'); // 

            $table->enum('mode', ['TEST', 'LIVE'])->default('TEST')->index();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_store_session');
    }
};
