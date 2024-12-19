<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // Link to clients table
            $table->foreignId('address_id')->constrained('addresses')->onDelete('cascade'); // Link to addresses table
            $table->enum('type', ['home', 'billing', 'shipping', 'custom'])->default('custom'); // Address type
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_addresses');
    }
};
