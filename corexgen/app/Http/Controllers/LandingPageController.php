<?php

namespace App\Http\Controllers;

use App\Models\CRM\CRMSettings;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    //

    /**
     * Landing page home
     */
    public function home()
    {



        /**
         *  header section
         *  heading
         *  subheading
         * 
         * features section
         * heading
         * subheading
         * 
         * featuers 
         * heading
         * details
         * 
         * solutions
         * heading
         * details
         * lists items multiple
         * 
         * plans section
         * heading
         * subheading
         * 
         * testimonail section
         * heading
         * 
         * testimonal details
         * custom logo
         * customer name
         * customer position (multiple)
         */




        return view('landing.index', [
            'logo' => $this->getLogo()
        ]);
    }

    /**
     * 
     * get logo
     */
    private function getLogo()
    {
        $query = CRMSettings::with('media');
        $logo = $query->where('is_tenant', 1)->where('name', 'tenant_company_logo')->first();

        return $logo?->media?->file_path ?? '/img/logo.png';
    }
}
