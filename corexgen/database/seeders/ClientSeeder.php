<?php

namespace Database\Seeders;

use App\Models\CategoryGroupTag;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [];

        $companyId = 10;

        $categoryIds = CategoryGroupTag::where('type', CATEGORY_GROUP_TAGS_TYPES['KEY']['categories'])->where('relation_type', CATEGORY_GROUP_TAGS_RELATIONS['KEY']['clients'])->where('status', 'active')->where('company_id', $companyId)->pluck('id')->toArray();


        $type = ['Individual', 'Company'];



        for ($i = 0; $i <= 100; $i++) {

            $typeTo = $type[array_rand($type)];

            $clients[] = [
                'type' => $typeTo,
                'company_name' => $typeTo == 'Company' ? fake()->company : null,
                'uuid' => Str::uuid(),
                'title' => ['Mr', 'Miss', 'Dr', 'Master'][array_rand(['Mr', 'Miss', 'Dr', 'Master'])], // Correct array_rand usage
                'first_name' => fake()->firstName(),
                'middle_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => json_encode([
                    fake()->safeEmail(),
                    fake()->safeEmail(),
                ]), // Multiple emails as JSON
                'phone' => json_encode([
                    fake()->phoneNumber(),
                    fake()->phoneNumber(),
                ]), // Multiple phone numbers as JSON
                'primary_email' => fake()->safeEmail(),
                'primary_phone' => fake()->phoneNumber(),
                'cgt_id' => $categoryIds[array_rand($categoryIds)],
                'company_id' => $companyId, // Generate random company_id
                'status' => CRM_STATUS_TYPES['CLIENTS']['STATUS']['ACTIVE'],
                'updated_at' => now(),
                'created_at' => now()
            ];
        }

        $chunkData = array_chunk($clients, 10);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($chunkData as $chunk) {
            DB::table('clients')->insert($chunk); // Use raw DB insert for performance
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
