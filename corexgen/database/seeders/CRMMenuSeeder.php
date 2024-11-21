<?php

namespace Database\Seeders;

use App\Models\CRM\CRMMenu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CRMMenuSeeder extends Seeder
{


    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('crm_menu')->delete();

        foreach (CRM_MENU_ITEMS as $category => $menuData) {
            // Insert parent menu
            $parentMenuId = DB::table('crm_menu')->insertGetId([
                'menu_name' => $category,
                'menu_url' => '',
                'parent_menu' => '1',
                'parent_menu_id' => null,
                'menu_icon' => $menuData['menu_icon'],
                'permission_id' => $menuData['permission_id'],
                'buyer_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert child menus
            foreach ($menuData['children'] as $menuName => $childMenuData) {
                DB::table('crm_menu')->insert([
                    'menu_name' => $menuName,
                    'menu_url' => $childMenuData['menu_url'],
                    'parent_menu' => '2',
                    'parent_menu_id' => $parentMenuId,
                    'menu_icon' => $childMenuData['menu_icon'],
                    'permission_id' => $childMenuData['permission_id'],
                    'buyer_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

