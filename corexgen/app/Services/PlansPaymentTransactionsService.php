<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Repositories\PlansPaymentTransactionsRepository;
use App\Traits\TenantFilter;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PlansPaymentTransactionsService
{

    use TenantFilter;
    protected $plansPaymentTransactionRepository;

    private $tenantRoute;


    public function __construct(PlansPaymentTransactionsRepository $plansPaymentTransactionRepository)
    {
        $this->plansPaymentTransactionRepository = $plansPaymentTransactionRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }

    public function getDatatablesResponse($request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->plansPaymentTransactionRepository->getTrnasactionQuery($request);

        // dd($query->get()->toArray());
        $module = PANEL_MODULES[$this->getPanelModule()]['companies'];

        return DataTables::of($query)
            ->addColumn('actions', function ($company) {
                return $this->renderActionsColumn($company);
            })
            ->editColumn('created_at', function ($company) {
                return Carbon::parse($company->created_at)->format('d M Y');
            })
            ->editColumn('name', function ($company) use ($module) {
                return "<a  class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $company->id) . "' target='_blank'>$company->name</a>";
            })
            ->editColumn('status', function ($company) {
                return $this->renderStatusColumn($company);
            })
            ->editColumn('plan_name', function ($company) {
                return $company->plan_name;
            })
            ->editColumn('billing_cycle', function ($company) {
                return $company->billing_cycle;
            })
            ->editColumn('start_date', function ($company) {
                return Carbon::parse($company->start_date)->format('d M Y');
            })
            ->editColumn('end_date', function ($company) {
                return Carbon::parse($company->end_date)->format('d M Y');
            })
            ->editColumn('next_billing_date', function ($company) {
                return Carbon::parse($company->next_billing_date)->format('d M Y');
            })
            ->rawColumns(['plan_name', 'billing_cycle', 'start_date', 'end_date', 'next_billing_date', 'actions', 'status', 'name']) // Add 'status' to raw columns
            ->make(true);
    }

    protected function renderActionsColumn($company)
    {


        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('COMPANIES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
            'id' => $company->id
        ])->render();
    }

    protected function renderStatusColumn($company)
    {


        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('COMPANIES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
            'id' => $company->id,
            'status' => [
                'current_status' => $company->status,
                'available_status' => CRM_STATUS_TYPES['COMPANIES']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['COMPANIES']['BT_CLASSES'],
            ]
        ])->render();
    }
}