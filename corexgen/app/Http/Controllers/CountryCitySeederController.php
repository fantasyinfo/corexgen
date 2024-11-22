<?php

namespace App\Http\Controllers;

use App\Jobs\SeedCountriesCities;
use Illuminate\Http\Request;

class CountryCitySeederController extends Controller
{
    /**
     * Run the country and city seeding in the background.
     *
     * @return \Illuminate\Http\Response
     */
    public function runSeeder()
    {
        // Dispatch the job to the queue
        SeedCountriesCities::dispatch();

        // Return a response to indicate the job has been queued
        return response()->json([
            'message' => 'Seeding job has been dispatched to the queue.',
            'status' => 'success',
        ]);
    }
}
