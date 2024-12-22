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

        Schema::create('crm_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->string('role_desc')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->enum('status', CRM_STATUS_TYPES['CRM_ROLES']['TABLE_STATUS']);
            $table->timestamps();


       
            // foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique(['role_name', 'company_id'], 'unique_role_name_per_company');
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
