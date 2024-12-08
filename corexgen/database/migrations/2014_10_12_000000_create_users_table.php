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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('status', CRM_STATUS_TYPES['USERS']['TABLE_STATUS']);
            $table->rememberToken();


            $table->boolean('is_tenant')->default(false);
            // nullble role id for tanent users
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();

            $table->string('profile_photo_path', 2048)->nullable();

            // forieng ids
            // Add tenant relationship

            $table->foreignId('current_team_id')->nullable();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
            $table->foreign('role_id')->references('id')->on('crm_roles')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');



            // indexes
            $table->index('email');
        
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
