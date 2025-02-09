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

        Schema::create('crm_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('parent_menu', ['1', '2'])->default('1');
            $table->bigInteger('parent_menu_id', )->nullable();
            $table->enum('for',['both','tenant','company'])->default('tenant');
            $table->boolean('is_feature')->default(false);
            $table->bigInteger('permission_id')->unique();

            $table->index(['parent_menu','permission_id','for']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_permissions');
    }
};
