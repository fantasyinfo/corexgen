<?php

namespace Database\Seeders;

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
        \App\Models\CRM\CRMRole::create([
            'role_name' => 'Super Admin',
            'role_desc' => 'for super admins',
            'status' => 'active',
            'buyer_id' => 1,
            'created_by' => 1,
        ]);
    }
}
