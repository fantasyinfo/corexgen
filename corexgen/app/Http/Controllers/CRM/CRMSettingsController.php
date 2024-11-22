<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CRMSettings;
use Illuminate\Http\Request;

class CRMSettingsController extends Controller
{
    //

    public function index()
    {

        $general_settings = CRMSettings::all();

        return view('dashboard.crm.settings.index', [
            'general_settings' => $general_settings,
            'title' => 'Settings Management'
        ]);
    }
}
