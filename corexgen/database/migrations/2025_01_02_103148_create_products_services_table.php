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
        Schema::create('products_services', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Product', 'Service'])->default('Product');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('rate');
            $table->integer('unit')->default(1);
            $table->enum('status', CRM_STATUS_TYPES['PRODUCTS_SERVICES']['TABLE_STATUS'])->default(CRM_STATUS_TYPES['PRODUCTS_SERVICES']['STATUS']['ACTIVE']);
            $table->softDeletes();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('cgt_id')->nullable()->constrained('category_group_tag')->onDelete('set null');
            $table->foreignId('tax_id')->nullable()->constrained('category_group_tag')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_services');
    }
};
