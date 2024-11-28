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
        Schema::create('subscriptions_usage', function (Blueprint $table) {
            $table->id();
            $table->integer('subscription_id');
            $table->string('module_name');
            $table->integer('current_usage')->default(0);
            $table->integer('max_limit');
            $table->timestamps();

            $table->index('subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions_usage');
    }
};
