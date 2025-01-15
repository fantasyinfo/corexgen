<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\PaymentGatewayFactory;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

// payment gateway routes handling for init url, success, cancel
class PaymentGatewayController extends Controller
{

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
    private $viewDir = 'dashboard.paymentgateway.';

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



    private PaymentGatewayFactory $gatewayFactory;

    public function __construct(PaymentGatewayFactory $gatewayFactory)
    {
        $this->gatewayFactory = $gatewayFactory;
    }

    /**
     * Initiate payment process
     * 
     * @param Request $request
     * @param string $gateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiate(Request $request, string $gateway = 'stripe')
    {
        try {
            // Validate payment details
            $validatedData = $request->validate([
                'plan_id' => 'required|exists:plans,id',
                'amount' => 'required|numeric|min:0',
                // Add more validation as needed
            ]);

            // Get payment gateway
            $paymentGateway = $this->gatewayFactory->create($gateway);

            // Initialize payment
            $paymentUrl = $paymentGateway->initialize([
                'amount' => $validatedData['amount'],
                'description' => "Plan Subscription",
                'metadata' => [
                    'plan_id' => $validatedData['plan_id'],
                    'is_company_registration' => true,
                ]
                // Add more payment details
            ]);

            // Redirect to payment gateway
            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            Log::error('Payment Initiation Failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Payment initialization failed');
        }
    }

    /**
     * Handle successful payment
     * 
     * @param Request $request
     * @param string $gateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleSuccess(Request $request, string $gateway)
    {
        try {
            $paymentGateway = $this->gatewayFactory->create($gateway);

            // Process payment and create user account
            return $paymentGateway->processPayment($request->all());
        } catch (\Exception $e) {
            Log::error('Payment Success Handling Failed: PaymentGatewayController::handleSuccess ' . $e->getMessage());

            return redirect()->route('compnay.landing-register')
                ->with('error', 'Unable to complete your registration');
        }
    }



    /**
     * Handle payment cancellation
     * 
     * @param string $gateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleCancel(string $gateway)
    {
        return redirect()->route('home')->with('warning', 'Payment was cancelled from gateway: ' . $gateway);
    }





    /**
     * View payment gateway and fetch
     */
    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        // Start a base query on the PaymentGateway model
        $query = PaymentGateway::query();

        // If user is *not* tenant (i.e., a company user),
        // eager-load only the settings that belong to that user's company.
        if (!Auth::user()->is_tenant) {
            $companyId = Auth::user()->company_id;

            // Eager-load paymentGatewaySettings but filter by this company_id
            $query->with([
                'paymentGatewaySettings' => function ($settingsQuery) use ($companyId) {
                    $settingsQuery->where('company_id', $companyId);
                }
            ]);
        }

        // Handle Ajax/DataTables
        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('actions', function ($paymentGateway) {
                    return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
                        'tenantRoute' => $this->tenantRoute,
                        'permissions' => PermissionsHelper::getPermissionsArray('PAYMENTGATEWAYS'),
                        'module' => PANEL_MODULES[$this->getPanelModule()]['paymentGateway'],
                        'id' => $paymentGateway->id
                    ])->render();
                })
                ->editColumn('created_at', function ($paymentGateway) {
                    return $paymentGateway->created_at->format('d M Y');
                })
                ->editColumn('logo', function ($paymentGateway) {
                    return "<img src='" . asset("/img/gateway/$paymentGateway->logo") . "' class='gateway_logo_img' />";
                })
                ->editColumn('status', function ($paymentGateway) {
                    // If tenant, read the status directly from the gateway table
                    // If company user, read from the *first* (and presumably only) PaymentGatewaySettings row
                    $status = Auth::user()->is_tenant
                        ? $paymentGateway->status
                        : ($paymentGateway->paymentGatewaySettings->first()?->status ?? 'Active');

                    return View::make(getComponentsDirFilePath('dt-status'), [
                        'tenantRoute' => $this->tenantRoute,
                        'permissions' => PermissionsHelper::getPermissionsArray('PAYMENTGATEWAYS'),
                        'module' => PANEL_MODULES[$this->getPanelModule()]['paymentGateway'],
                        'id' => $paymentGateway->id,
                        'status' => [
                            'current_status' => $status,
                            'available_status' => ['Active', 'Inactive'],
                            'bt_class' => [
                                'Active' => 'success',
                                'Inactive' => 'danger'
                            ],
                        ],
                    ])->render();
                })
                ->editColumn('mode', function ($paymentGateway) {
                    // If tenant, read the status directly from the gateway table
                    // If company user, read from the *first* (and presumably only) PaymentGatewaySettings row
                    return Auth::user()->is_tenant
                        ? $paymentGateway->mode
                        : ($paymentGateway->paymentGatewaySettings->first()?->mode ?? 'TEST');


                })
                ->rawColumns(['actions', 'mode', 'status', 'logo'])
                ->make(true);
        }

        // Non-ajax request: render the index view
        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Gateway Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PAYMENTGATEWAYS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['paymentGateway'],
        ]);
    }


    /**
     * edit payment gateway
     */

    public function edit($id)
    {
        $query = PaymentGateway::query()->where('id', $id);

        // If user is NOT tenant, we only want the PaymentGatewaySettings for that user's company
        if (!Auth::user()->is_tenant) {
            $companyId = Auth::user()->company_id;
            $query->with([
                'paymentGatewaySettings' => function ($settingsQuery) use ($companyId) {
                    $settingsQuery->where('company_id', $companyId);
                }
            ]);
        }

        $gateway = $query->firstOrFail();

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Payment Gateway',
            'gateway' => $gateway,
            'isTenant' => Auth::user()->is_tenant
        ]);
    }


    /**
     * Update payment gateway
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'config_key' => 'required|string',
            'config_value' => 'required|string',
            'mode' => 'required|in:LIVE,TEST',
        ]);

        $this->tenantRoute = $this->getTenantRoute();

        try {
            // Fetch the PaymentGateway
            $gateway = PaymentGateway::query()
                ->with(['paymentGatewaySettings'])
                ->where('id', $request->id)
                ->firstOrFail();

            if (Auth::user()->is_tenant) {
                // Tenant => update directly in the main PaymentGateway table
                $gateway->update($validatedData);
            } elseif (Auth::user()->company_id) {
                // Company user => update or create in PaymentGatewaySettings for this company & gateway
                $gateway->paymentGatewaySettings()
                    ->updateOrCreate([
                        'company_id' => Auth::user()->company_id,
                        'payment_gateway_id' => $gateway->id,
                    ], $validatedData);
            } else {
                // Optional fallback if user is neither tenant nor has a company_id
                return redirect()->back()
                    ->with('error', 'Unable to determine tenant or company for this user. Cannot update gateway.');
            }

            // Redirect with success message
            return redirect()
                ->route($this->tenantRoute . 'paymentGateway.index')
                ->with('success', 'Payment Gateway updated successfully.');
        } catch (\Exception $e) {
            // Handle any errors
            return redirect()->back()
                ->with('error', 'An error occurred while updating the gateway: ' . $e->getMessage());
        }
    }


    /**
     * Chaning the status 
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatus($id, $status)
    {
        try {
            // Apply tenant filtering and find role
            $gateway = PaymentGateway::query()->with(['paymentGatewaySettings'])->where('id', $id)->firstOrFail();
            if (Auth::user()->is_tenant) {
                $gateway->update(['status' => $status]);
            } else if (Auth::user()->company_id != null) {
                $gateway->paymentGatewaySettings()->updateOrCreate([
                    'company_id' => Auth::user()->company_id,
                    'payment_gateway_id' => $id
                ], ['status' => $status]);
            }
            // Redirect with success message
            return redirect()->back()->with('success', 'Gateway status changed successfully.');
        } catch (\Exception $e) {
            // Handle any status change errors
            return redirect()->back()->with('error', 'Failed to change the Gateway status: ' . $e->getMessage());
        }
    }



    /**
     * handle Payments response from gateway payment success
     */

    public function handlePaymentGatewaysSuccessResponse(array $successPaymentData)
    {
        Log::info('Success Payment Data', $successPaymentData);

        $paymentDetails = [
            'payment_gateway' => $successPaymentData['payment_gateway'] ?? '',
            'payment_type' => $successPaymentData['payment_type'] ?? '',
            'transaction_reference' => $successPaymentData['transaction_reference'] ?? '',
            'transaction_id' => $successPaymentData['transaction_id'] ?? '',
            'amount' => $successPaymentData['amount'] ?? '',
            'currency' => $successPaymentData['currency'] ?? '',
            'company_id' => $successPaymentData['company_id'] ?? '',
            'plan_id' => $successPaymentData['plan_id'] ?? '',
            'response' => $successPaymentData['response'] ?? [],
            'invoice_uuid' => $successPaymentData['invoice_uuid'] ?? ''
        ];

        // Fix: Convert string "true"/"false" to actual boolean and use strict comparison
        if (isset($successPaymentData['is_plan_upgrade']) && filter_var($successPaymentData['is_plan_upgrade'], FILTER_VALIDATE_BOOLEAN) === true) {
            Log::info('Calling Plan Upgrade', ['is_true' => $successPaymentData['is_plan_upgrade']]);
            return app(CompanyRegisterController::class)->upgradePlanForCompany($paymentDetails);


        } else if (isset($successPaymentData['is_company_registration']) && filter_var($successPaymentData['is_company_registration'], FILTER_VALIDATE_BOOLEAN) === true) {
            Log::info('Calling Company Register', ['is_true' => $successPaymentData['is_company_registration']]);
            return app(CompanyRegisterController::class)->storeCompnayAfterPaymentOnboading($paymentDetails);


        } else if (isset($successPaymentData['is_invoice_paying']) && filter_var($successPaymentData['is_invoice_paying'], FILTER_VALIDATE_BOOLEAN) === true) {
            Log::info('Calling Pay Invoice', ['is_true' => $successPaymentData['is_invoice_paying']]);
            return app(InvoiceController::class)->storeInvoicePaymentAfterPaid($paymentDetails);


        }

        dd($successPaymentData);
    }
}
