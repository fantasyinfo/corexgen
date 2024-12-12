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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->unique(); // 
            $table->string('official_website')->nullable(); //
            $table->string('logo')->nullable(); // 
            $table->string('type')->nullable()->index();
            $table->string('config_key')->unique(); // 
            $table->text('config_value'); // 
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->index();
            $table->enum('mode', ['TEST', 'LIVE'])->default('TEST')->index();

            // Composite index optimization
            $table->index(['name', 'type', 'status', 'mode']);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
