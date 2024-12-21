<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\PaymentGatewayFactory;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
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




    public function index(Request $request)
    {
        //

        $query = PaymentGateway::query();
        $this->tenantRoute = $this->getTenantRoute();
        // Server-side DataTables response
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
                ->editColumn('created_at', fn($paymentGateway) => $paymentGateway->created_at->format('d M Y'))
                ->editColumn('logo', fn($paymentGateway) => "<img src='" . asset("/img/gateway/$paymentGateway->logo") . "' class='gateway_logo_img' />")

                ->editColumn('status', function ($paymentGateway) {
                    return View::make(getComponentsDirFilePath('dt-status'), [

                        'tenantRoute' => $this->tenantRoute,
                        'permissions' => PermissionsHelper::getPermissionsArray('PAYMENTGATEWAYS'),
                        'module' => PANEL_MODULES[$this->getPanelModule()]['paymentGateway'],
                        'id' => $paymentGateway->id,
                        'status' => [
                            'current_status' => $paymentGateway->status,
                            'available_status' => ['Active', 'Inactive'],
                            'bt_class' => ['Active' => 'success', 'Inactive' => 'danger'],

                        ]
                    ])->render();
                })
                ->rawColumns(['actions', 'status', 'logo'])
                ->make(true);
        }


        // Render index view with filterss
        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Gateway Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PAYMENTGATEWAYS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['paymentGateway'],
        ]);
    }

    public function edit($id)
    {
        // Apply tenant filtering to gateway query
        $query = PaymentGateway::query()->where('id', $id);
        $gateway = $query->firstOrFail();

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Payment Gateway',
            'gateway' => $gateway
        ]);
    }

    public function update(Request $request)
    {

        $validatedData = $request->validate([
            'config_key' => 'required|string',
            'config_value' => 'required|string',
            'mode' => 'required|in:LIVE,TEST',
        ]);

        $this->tenantRoute = $this->getTenantRoute();
        
        try {
            // Validate and update role
            $query = PaymentGateway::query()->where('id', $request->id);
            $query->update($validatedData);

            // Redirect with success message
            return redirect()->route($this->tenantRoute . 'paymentGateway.index')
                ->with('success', 'Payment Gateway updated successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role update
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
            $query = PaymentGateway::query()->where('id', $id);
            $query->update(['status' => $status]);
            // Redirect with success message
            return redirect()->back()->with('success', 'Gateway status changed successfully.');
        } catch (\Exception $e) {
            // Handle any status change errors
            return redirect()->back()->with('error', 'Failed to change the Gateway status: ' . $e->getMessage());
        }
    }
}