<?php

namespace Database\Seeders;

use App\Models\CRM\CRMMenu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CRMMenuSeeder extends Seeder
{
    private $menuItems = [
        'Dashboard' => [
            'menu_icon' => 'feather-airplay',
            'children' => [
                'CRM' => ['menu_url' => 'home', 'menu_icon' => 'feather-corner-down-right']
            ]
        ],
        'Roles & Users' => [
            'menu_icon' => 'feather-user-plus',
            'children' => [
                'Role' => ['menu_url' => 'crm.role.index', 'menu_icon' => 'feather-corner-down-right'],
                'Create Role' => ['menu_url' => 'crm.role.create', 'menu_icon' => 'feather-corner-down-right'],
                'Users' => ['menu_url' => 'crm.users.index', 'menu_icon' => 'feather-corner-down-right'],
                'Create Users' => ['menu_url' => 'crm.users.create', 'menu_icon' => 'feather-corner-down-right']
            ]
        ]
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('crm_menu')->delete();

        foreach ($this->menuItems as $category => $menuData) {
            // Insert parent menu
            $parentMenuId = DB::table('crm_menu')->insertGetId([
                'menu_name' => $category,
                'menu_url' => '',
                'parent_menu' => '1',
                'parent_menu_id' => null,
                'menu_icon' => $menuData['menu_icon'],
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
                    'buyer_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

