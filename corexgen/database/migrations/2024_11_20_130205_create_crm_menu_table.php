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
        Schema::create('crm_menu', function (Blueprint $table) {
            $table->id();
            $table->string('menu_name');
            $table->string('menu_url');
            $table->enum('parent_menu',['1','2'])->default('1');
            $table->bigInteger('parent_menu_id',)->nullable();
            $table->string('menu_icon')->nullable();
            $table->bigInteger('buyer_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_menu');
    }
};
