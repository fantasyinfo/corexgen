<?php

namespace App\Http\Controllers;

use App\Http\Requests\CRM\CompaniesRequest;
use App\Models\Plans;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // 'currency' => 'USD',
        // 'payment_gateway' => $paymentDetails['payment_gateway'] ?? 'COD',
        // 'payment_type' => $paymentDetails['payment_type'] ?? 'OFFLINE',
        // 'transaction_reference' => $paymentDetails['transaction_reference'] ?? null,

        // register the company
        $company = $companyService->createCompany($request->validated());

        // login user as a compnay owner

        $user = User::where('company_id', $company->id)
        ->where('role_id', null)
        ->where('is_tenant', 0)
        ->first();

        $guard = Auth::guard('web')->loginUsingId($user->id);


        // todo:: after register the compnay redirect to compnay panel
        return redirect()->route(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.home');
    }
}
