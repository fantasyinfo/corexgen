<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{

    use WithoutModelEvents;
    public function run(): void
    {
   
        $usersArray = [];

        for ($i = 0; $i <= 100; $i++) {
            $usersArray[] = [
                'name' => fake()->name(),
                'email' => fake()->safeEmail(), // Use static emails for debugging
                'password' => bcrypt('password'), // Hash passwords
                'role_id' => '3',
                'is_tenant' => true,
                'tenant_id' => '1',
            ];
        }

        $chunkData = array_chunk($usersArray, 10);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($chunkData as $chunk) {
            DB::table('users')->insert($chunk); // Use raw DB insert for performance
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
