<?php

namespace Database\Seeders;

use App\Models\CategoryGroupTag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryGroupTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $insertArray = [];
        $clientsCategory = ['VIP', 'Normal', 'High Budget', 'Low Budget'];
        $colors = [ '#673DE6', '#2F1C6A', '#00B090', '#FF3C5C', '#FFB800',  '#2C5CC5'];

        // clients category
        foreach($clientsCategory as $cc){
            $insertArray[] = [
                'name' => $cc,
                'color' => array_rand($colors),
                'relation_type' => 'clients',
                'type' => 'categories',
                'status' => 'active',
                'company_id' => null
            ] ;
        }
        CategoryGroupTag::insert($insertArray);
    }
}
