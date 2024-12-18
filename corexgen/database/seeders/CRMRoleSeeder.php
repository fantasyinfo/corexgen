<?php

namespace Database\Seeders;

use App\Models\CRM\CRMRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CRMRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
       CRMRole::create([
            'role_name' => 'Super Admin',
            'role_desc' => 'for super admins',
        ]);
    }

    // public function run(): void
    // {
    //     ini_set('memory_limit', '-1');
    //     // Number of records to generate
    //     $totalRecords = 10000; // Adjust as needed
    //     $chunkSize = 5000; // Number of records per insert chunk
    
    //     for ($i = 0; $i < $totalRecords; $i += $chunkSize) {
    //         $createRoles = [];
    
    //         // Generate a chunk of roles
    //         for ($j = 0; $j < $chunkSize; $j++) {
    //             $createRoles[] = [
    //                 'role_name' => fake()->jobTitle(), // Faker method for role names
    //                 'role_desc' => fake()->sentence(), // Faker method for descriptions
    //                 'status' => 'active', // Example static value
    //                 'created_at' => now(), // Ensure timestamps are handled
    //                 'updated_at' => now(),
    //             ];
    //         }
    
    //         // Insert the chunk into the database
    //         CRMRole::insert($createRoles);
    //     }
    // }
    
}
