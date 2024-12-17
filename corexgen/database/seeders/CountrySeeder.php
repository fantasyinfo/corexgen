<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table before seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Country::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
     

        $countryArray = [];

        foreach (countriesList() as $key => $country) {
            array_push($countryArray, [
                'name' => $country,
                'code' => $key,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert multiple records at once
        Country::insert($countryArray);
    }
}
