<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Repositories\PlansPaymentTransactionsRepository;
use App\Traits\TenantFilter;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;

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

        //  dd($query->toArray());

        $module = PANEL_MODULES[$this->getPanelModule()]['planPaymentTransaction'];
        $cmodule = PANEL_MODULES[$this->getPanelModule()]['companies'];

        return DataTables::of($query)
            // ->addColumn('actions', function ($ppT) {
            //     return $this->renderActionsColumn($ppT);
            // })
            ->editColumn('created_at', function ($ppT) {
                return Carbon::parse($ppT->created_at)->format('d M Y');
            })
            ->editColumn('transaction_date', function ($ppT) {
                return Carbon::parse($ppT->transaction_date)->format('d M Y');
            })
            ->editColumn('name', function ($ppT) use ($cmodule) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $cmodule . '.view', $ppT->company->id) . "' target='_blank'>" 
                       . $ppT->company->name . "</a>";
            })
            // ->editColumn('status', function ($ppT) {
            //     return $this->renderStatusColumn($ppT);
            // })
            ->editColumn('plan_name', function ($ppT) {
                return $ppT->plans->name;
            })
            ->editColumn('amount', function ($ppT) {
                return $ppT->amount;
            })
            ->editColumn('currecny', function ($ppT) {
                return $ppT->currency;
            })
            ->editColumn('payment_gateway', function ($ppT) {
                return $ppT->payment_gateway;
            })
            ->editColumn('start_date', function ($ppT) {
                return Carbon::parse($ppT->subscription->start_date)->format('d M Y');
            })
            ->rawColumns(['plan_name', 'start_date', 'status', 'name']) // Add 'status' to raw columns
            ->make(true);
    }

    // protected function renderActionsColumn($company)
    // {


    //     return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
    //         'tenantRoute' => $this->tenantRoute,
    //         'permissions' => PermissionsHelper::getPermissionsArray('PAYMENTSTRANSACTIONS'),
    //         'module' => PANEL_MODULES[$this->getPanelModule()]['planPaymentTransaction'],
    //         'id' => $company->id
    //     ])->render();
    // }

    // protected function renderStatusColumn($ppT)
    // {


    //     return View::make(getComponentsDirFilePath('dt-status'), [
    //         'tenantRoute' => $this->tenantRoute,
    //         'permissions' => PermissionsHelper::getPermissionsArray('PAYMENTSTRANSACTIONS'),
    //         'module' => PANEL_MODULES[$this->getPanelModule()]['planPaymentTransaction'],
    //         'id' => $ppT->id,
    //         'status' => [
    //             'current_status' => $ppT->status,
    //             'available_status' => CRM_STATUS_TYPES['PAYMENTSTRANSACTIONS']['STATUS'],
    //             'bt_class' => CRM_STATUS_TYPES['PAYMENTSTRANSACTIONS']['BT_CLASSES'],
    //         ]
    //     ])->render();
    // }
}