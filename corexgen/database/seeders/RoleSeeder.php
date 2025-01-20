<?php

namespace Database\Seeders;

use App\Models\CRM\CRMRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    // php artisan db:seed --class=RoleSeeder
    // php artisan db:seed --class=UserSeeder
    // php artisan db:seed --class=LeadsSeeder
    // php artisan db:seed --class=ClientSeeder
    // php artisan db:seed --class=TasksSeeder



    public function run(): void
    {
        //

        $companyId = 1;

        $rolesArray = [];

        for ($i = 0; $i <= 20; $i++) {
            $rolesArray[] = [
                'role_name' => fake()->unique()->name(),
                'role_desc' => fake()->text(15),
                'company_id' => $companyId,
                'status' => CRM_STATUS_TYPES['CRM_ROLES']['STATUS']['ACTIVE'],
                'updated_at' => now(),
                'created_at' => now()
            ];
        }

        CRMRole::insert($rolesArray);



    }
}
