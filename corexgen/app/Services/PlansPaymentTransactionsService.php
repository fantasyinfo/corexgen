<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\Company;
use App\Repositories\PlansPaymentTransactionsRepository;
use App\Traits\TenantFilter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

        $query = $this->plansPaymentTransactionRepository->getTransactionQuery($request);

       // dd($query->get()->toArray());


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
                if(isset($ppT?->company) && isset($ppT?->company?->name)){
                    return "<a class='dt-link' href='" . route($this->tenantRoute . $cmodule . '.view', $ppT?->company?->id) . "' target='_blank'>"
                    . $ppT?->company?->name . "</a>";
                }else {
                    return '';
                }
   
            })
            ->editColumn('subscription.start_date', function ($ppT) {
                return Carbon::parse($ppT->subscription->start_date)->format('d M Y');
            })
            ->rawColumns([  'name','subscription.start_date','created_at','transaction_date']) // Add 'status' to raw columns
            ->make(true);
    }

    public function getDatatablesResponseForSub($request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->plansPaymentTransactionRepository->getSubscriptionsQuery($request);

        //   dd($query->toArray());

        $module = PANEL_MODULES[$this->getPanelModule()]['subscriptions'];
        $cmodule = PANEL_MODULES[$this->getPanelModule()]['companies'];

        return DataTables::of($query)
            ->editColumn('created_at', function ($ppT) {
                return Carbon::parse($ppT->created_at)->format('d M Y');
            })
            ->editColumn('start_date', function ($ppT) {
                return Carbon::parse($ppT->start_date)->format('d M Y');
            })
            ->editColumn('end_date', function ($ppT) {
                return Carbon::parse($ppT->end_date)->format('d M Y');
            })
            ->editColumn('next_billing_date', function ($ppT) {
                return Carbon::parse($ppT->next_billing_date)->format('d M Y');
            })
            ->editColumn('upgrade_date', function ($ppT) {
                if($ppT->upgrade_date == null) return;
                return Carbon::parse($ppT->upgrade_date)->format('d M Y');
            })
            ->editColumn('name', function ($ppT) use ($cmodule) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $cmodule . '.view', $ppT->company->id) . "' target='_blank'>"
                    . $ppT->company->name . "</a>";
            })
            ->rawColumns([ 'start_date', 'end_date', 'upgrade_date','next_billing_date', 'status', 'name'])
            ->make(true);
    }


}