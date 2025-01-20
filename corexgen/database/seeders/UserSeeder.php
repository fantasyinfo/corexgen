<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{

    use WithoutModelEvents;

    protected $companyId;

    public function __construct( int $companyId = 1) {
        $this->companyId = $companyId;
    }
    public function run(): void
    {
   
        $usersArray = [];

        $rolesIds = DB::table(table: 'crm_roles')->where('company_id', $this->companyId)->pluck('id')->toArray();

        // info('Roles',[$rolesIds]);

        for ($i = 0; $i <= 100; $i++) {
            $usersArray[] = [
                'name' => fake()->name(),
                'email' => fake()->safeEmail(), // Use static emails for debugging
                'password' => 1234, // Hash passwords
                'role_id' => $rolesIds[array_rand($rolesIds)],
                'is_tenant' => false,
                'company_id' => $this->companyId,
                'status' => CRM_STATUS_TYPES['USERS']['STATUS']['ACTIVE'],
                'updated_at' => now(),
                'created_at' => now()
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
