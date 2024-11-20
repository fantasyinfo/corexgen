<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Models\CRM\CRMPermissions;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CRMPermissionsSeeder extends Seeder
{
    private $permissions = [
        'CRM_DASHBOARD' => ['READ'],
        'CRM_ROLE' => ['CREATE', 'READ', 'READ_ALL', 'UPDATE', 'DELETE'],
        'CRM_USERS' => ['CREATE', 'READ', 'READ_ALL', 'UPDATE', 'DELETE'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table to remove all entries and reset auto-increment IDs
        DB::table('crm_permissions')->truncate();

        foreach ($this->permissions as $name => $values) {
            foreach ($values as $permission) {
                DB::table('crm_permissions')->insert([
                    'name' => $name . '_' . $permission,
                    'buyer_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

