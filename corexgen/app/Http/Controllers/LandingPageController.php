<?php

namespace App\Http\Controllers;

use App\Models\CRM\CRMSettings;
use App\Models\LandingPage;
use App\Models\Plans;
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


         if (getModule(1) == 'company') {
            return redirect()->route('login');
        }

        $landingPage = LandingPage::all();
        $plans = Plans::with(['planFeatures'])->where('status', CRM_STATUS_TYPES['PLANS']['STATUS']['ACTIVE'])->get();

        $settings = CRMSettings::query()->where('is_tenant', '1')->get();

        return view('landing.index', [
            'logo' => $this->getLogo(),
            'landingPage' => $landingPage,
            'plans' => $plans,
            'settings' => $settings
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
