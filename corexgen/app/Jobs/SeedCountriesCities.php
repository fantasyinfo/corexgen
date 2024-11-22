<?php

namespace App\Jobs;

use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;


class SeedCountriesCities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Load the JSON file from the public folder
        $jsonFilePath = public_path('countries+cities.json');

        if (File::exists($jsonFilePath)) {
            $jsonData = File::get($jsonFilePath);
            $countriesData = json_decode($jsonData, true);

            // delete the exiting country and city first
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            City::truncate();
            Country::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            foreach ($countriesData as $country) {
                // Store the country data
                $storedCountry = Country::create([
                    'name' => $country['name'],
                    'code' => $country['iso3']
                ]);

                // Store the cities data for this country
                foreach ($country['cities'] as $city) {
                    City::create([
                        'name' => $city['name'],
                        'country_id' => $storedCountry->id,
                    ]);
                }
            }
        } else {
            echo "File not found at $jsonFilePath\n";
        }
    }
}
