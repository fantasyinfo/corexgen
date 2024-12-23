<?php

namespace Database\Seeders;

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

        for ($i = 0; $i <= 100; $i++) {
            $clients[] = [
                'type' => 'Individual',
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
              
                'company_id' => 1, // Generate random company_id
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
