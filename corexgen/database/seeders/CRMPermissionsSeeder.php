<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Models\CRM\CRMPermissions;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CRMPermissionsSeeder extends Seeder
{


    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table to remove all entries and reset auto-increment IDs
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('crm_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach (CRMPERMISSIONS as $name => $values) {

            // Insert the parent permission and get its ID
            $parentMenuId = DB::table('crm_permissions')->insertGetId([
                'name' => $values['name'],
                'parent_menu' => '1',
                'parent_menu_id' => null,
                'for' => $values['for'],
                'is_feature' => isset($values['is_feature']) ? $values['is_feature'] : false,
                'permission_id' => $values['id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($values['children'] as $keys => $data) {
                // Insert child permissions with the parent_menu_id
                DB::table('crm_permissions')->insert([
                    'name' => $data,
                    'parent_menu' => '2',
                    'for' => $values['for'],
                    'parent_menu_id' => $parentMenuId,
                    'permission_id' => $keys,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
