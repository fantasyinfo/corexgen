<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Company;
use App\Models\PaymentGateway;
use App\Models\Plans;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CompanyService;
use App\Services\PaymentGatewayFactory;
use App\Traits\TenantFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanUpgrade extends Controller
{

    //
    use TenantFilter;

    /**
     * Number of items per page for pagination
     * @var int
     */
    protected $perPage = 10;

    /**
     * Tenant-specific route prefix
     * @var string
     */
    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'dashboard.planupgrade.';

    /**
     * Generate full view file path
     * 
     * @param string $filename
     * @return string
     */
    private function getViewFilePath($filename)
    {
        return $this->viewDir . $filename;
    }


    /**
     * Display list of plans 
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $plans = Plans::query()->with('planFeatures')->where('status', CRM_STATUS_TYPES['PLANS']['STATUS']['ACTIVE'])->get();
        $subscription = Subscription::where('company_id', $companyId)->latest()->first();
        $company = Company::find($companyId);
        // dd($subscription);
        $this->tenantRoute = $this->getTenantRoute();


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Memberships Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PLANUPGRADE'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['planupgrade'],
            'plans' => $plans,
            'current_plan_id' => $company->plan_id,
            'renew_at' => Carbon::make($subscription->next_billing_date)->format('d M, Y'),
            'payment_gateways' => PaymentGateway::where('status', 'Active')->get()
        ]);
    }

    public function upgrade(Request $request, CompanyService $companyService, PaymentGatewayFactory $paymentGatewayFactory)
    {

        // payment gateway
        // update company plan id
        // create payment transaction
        // create new subscrition


        $request->validate([
            'plan_id' => 'required|exists:plans,id'
        ]);

        $companyId = Auth::user()->company_id;
        $company = Company::find($companyId);

        $planOfferPrice = Plans::find($request->plan_id);
        if ($planOfferPrice->offer_price <= 0) {

            $paymentDetails = [
                'payment_gateway' => 'COD',
                'payment_type' => 'OFFLINE',
                'transaction_reference' => json_encode([]),
                'transaction_id' => null,
                'amount' => 00,
                'currency' => 'USD', // tmp
                'company_id' => $companyId,
                'plan_id' => $planOfferPrice->id,
            ];

            $paymentTransaction = $companyService->createPaymentTransaction($planOfferPrice->id, $companyId, $paymentDetails);

            $user = $this->findCompanyOwner($company);

            $companyService->givePermissionsToCompany($company, $user);

            $company->plan_id = $planOfferPrice->id;
            $company->save();

            return redirect()->back()->with('success', 'Plan has been changed successfully.');

        } else {

            $validatedData = [
                'plan_id' => $planOfferPrice->id,
                'plan_name' => $planOfferPrice->name,
                'plan_price' => $planOfferPrice->offer_price,
                'gateway' => $request->gateway
            ];


            // Validate required payment parameters
            $this->validatePaymentParameters($validatedData);

            // Get selected payment gateway
            $gateway = $validatedData['gateway'] ?? 'stripe';

            // Create payment gateway instance
            $paymentGateway = $paymentGatewayFactory->create($gateway);

            // Prepare payment details
            $paymentDetails = [
                'amount' => $validatedData['plan_price'],
                'description' => "Changed To - {$validatedData['plan_name']} Plan",
                'currency' => getSettingValue('Currency Code'),
                'metadata' => [
                    'plan_id' => $validatedData['plan_id'],
                    'company_registration' => true,
                    'company_id' => $company->id,
                    'is_plan_upgrade' => true
                ]
            ];

            \Log::info('Payment Details Passed', ['plan' => $paymentDetails]);
            // Initialize payment
            $paymentUrl = $paymentGateway->initialize($paymentDetails);
            return redirect()->away($paymentUrl);
        }


    }

    private function findCompanyOwner($company)
    {
        $user = User::where('company_id', $company->id)
            ->where('role_id', null)
            ->where('is_tenant', 0)
            ->first();

        if (!$user) {
            throw new \Exception('Company owner user not found');
        }

        return $user;
    }

    /**
     * Validate payment parameters
     * 
     * @param array $validatedData
     * @throws \Exception
     */
    private function validatePaymentParameters(array $validatedData)
    {
        $requiredFields = [
            'plan_id',
            'plan_name',
            'plan_price',
            'gateway'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($validatedData[$field])) {
                throw new \Exception("Missing required payment parameter: {$field}");
            }
        }
    }
}
