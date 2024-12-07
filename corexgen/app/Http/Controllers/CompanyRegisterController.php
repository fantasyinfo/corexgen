<?php

namespace App\Http\Controllers;

use App\Http\Requests\CRM\CompaniesRequest;
use App\Models\Plans;
use App\Services\CompanyService;
use Illuminate\Http\Request;

class CompanyRegisterController extends Controller
{
    //

    public function register()
    {
        $plans = Plans::where('status', CRM_STATUS_TYPES['PLANS']['STATUS']['ACTIVE'])->get();
        return view('landing.register', ['plans' => $plans]);
    }

    public function registerCompany(CompaniesRequest $request, CompanyService $companyService){

        // todo:: please capture the payment now its the plan is paid
        // register the company
        $company = $companyService->createCompany($request->validated());

        // todo:: after register the compnay redirect to compnay panel
    }
}
