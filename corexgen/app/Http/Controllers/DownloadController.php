<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    //

    public function countries(){
        $countries = Country::all(['id', 'name']);
        $csvData = "ID,Name\n";
    
        foreach ($countries as $country) {
            $csvData .= "{$country->id},{$country->name}\n";
        }
    
        $filename = "countries.csv";
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=$filename");
    }
}
