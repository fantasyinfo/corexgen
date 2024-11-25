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
}
