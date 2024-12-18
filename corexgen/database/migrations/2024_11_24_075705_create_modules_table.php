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


        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('version');
            $table->text('description');
            $table->json('providers');
            $table->json('settings')->nullable();
            $table->string('path');
            $table->enum('panel_type', array_keys(PANEL_TYPES))->default(PANEL_TYPES['SUPER_PANEL']);
            $table->enum('status', ['active', 'inactive']);

            $table->index(['name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
