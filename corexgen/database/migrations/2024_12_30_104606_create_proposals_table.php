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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('_prefix')->default('PRO');
            $table->string('uuid');
            $table->string('_id');
            $table->string('title');
            $table->string('url')->unique();
            $table->string('value')->nullable();
            $table->text('details')->nullable();
            $table->morphs('typable'); // Adds `typable_id` and `typable_type`
            $table->date('creating_date')->default(now());
            $table->date('valid_date')->default(now())->nullable();
            $table->json('accepted_details')->nullable();
            $table->json('product_details')->nullable();
            $table->enum('status', CRM_STATUS_TYPES['PROPOSALS']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['PROPOSALS']['STATUS']['OPEN']);

            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->unsignedBigInteger('company_id');


            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('assign_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('set null');



            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
