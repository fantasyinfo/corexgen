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
        Schema::create('notification_record', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->enum('type', ['mail','notification','sms']);
            $table->json('data')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();

            $table->foreign('company_id')
            ->references('id')
            ->on('companies')
            ->onDelete('set null');

            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_record');
    }
};
