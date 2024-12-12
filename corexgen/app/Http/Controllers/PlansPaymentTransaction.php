<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Repositories\PlansPaymentTransactionsRepository;
use App\Services\PlansPaymentTransactionsService;
use App\Services\UserService;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;

class PlansPaymentTransaction extends Controller
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
    private $viewDir = 'dashboard.planspaymenttransaction.';

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


    protected $ppTRepository;
    protected $ppTService;

    public function __construct(
        PlansPaymentTransactionsRepository $ppTRepository,
        PlansPaymentTransactionsService $ppTService
    ) {
        $this->ppTRepository = $ppTRepository;
        $this->ppTService = $ppTService;
    }

    /**
     * Display list of users with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Ajax DataTables request
        if ($request->ajax()) {
            return $this->ppTService->getDatatablesResponse($request);
        }

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Payment Transactions Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PAYMENTSTRANSACTIONS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['planPaymentTransaction'],
        ]);
    }


    public function subscriptions(Request $request){
         // Ajax DataTables request
         if ($request->ajax()) {
            return $this->ppTService->getDatatablesResponseForSub($request);
        }

        return view($this->getViewFilePath('subscriptions'), [
            'filters' => $request->all(),
            'title' => 'Subscriptions Management',
            'permissions' => PermissionsHelper::getPermissionsArray('SUBSCRIPTIONS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['subscriptions'],
        ]);
    }


}
