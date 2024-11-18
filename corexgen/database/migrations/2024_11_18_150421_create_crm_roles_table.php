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

        Schema::create('crm_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->string('role_desc')->nullable();
            $table->bigInteger('buyer_id')->default(1);
            $table->bigInteger('created_by')->default(1);
            $table->enum('status', ['active', 'deactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_roles');
    }
};
