<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('category_group_tag', function (Blueprint $table) {

            // Define your updated constants
            $relationTypes = implode("','", CATEGORY_GROUP_TAGS_RELATIONS['TABLE_STATUS']);
            $relationDefault = CATEGORY_GROUP_TAGS_RELATIONS['STATUS']['clients'];

            $typeValues = implode("','", CATEGORY_GROUP_TAGS_TYPES['TABLE_STATUS']);
            $typeDefault = CATEGORY_GROUP_TAGS_TYPES['STATUS']['categories'];

            // Modify the 'relation_type' column
            DB::statement("
            ALTER TABLE category_group_tag 
            MODIFY COLUMN relation_type 
            ENUM('$relationTypes') 
            DEFAULT '$relationDefault'
        ");

            // Modify the 'type' column
            DB::statement("
            ALTER TABLE category_group_tag 
            MODIFY COLUMN type 
            ENUM('$typeValues') 
            DEFAULT '$typeDefault'
        ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       //
    }
};
