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
        Schema::create('crm_menu', function (Blueprint $table) {
            $table->id();
            $table->string('menu_name');
            $table->string('menu_url');
            $table->enum('parent_menu', ['1', '2'])->default('1');
            $table->bigInteger('parent_menu_id', )->nullable();
            $table->bigInteger('permission_id', )->nullable();
            $table->string('menu_icon')->nullable();
            $table->enum('panel_type', array_keys(PANEL_TYPES));
            $table->boolean('is_default')->default(false);
            $table->string('feature_type')->nullable();
            $table->enum('module_type',['company','saas'])->default('saas');
            $table->unique(['panel_type', 'permission_id'], 'panel_type_permission_id_unique');
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
