<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Repositories\ProductServicesRepository;
use App\Traits\TenantFilter;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class ProductServicesService
{
    use TenantFilter;


    protected $productSericeRepository;

    private $tenantRoute;

    public function __construct(ProductServicesRepository $productSericeRepository)
    {
        $this->productSericeRepository = $productSericeRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }

    public function getDatatablesResponse($request)
    {
        $this->tenantRoute = $this->getTenantRoute();
        // Add your logic here
        $query = $this->productSericeRepository->getProductsQuery($request);

         // dd($query->get()->toArray());
         $module = PANEL_MODULES[$this->getPanelModule()]['products_services'];

         return DataTables::of($query)
            ->addColumn('actions', function ($product) {
                return $this->renderActionsColumn($product);
            })
            ->editColumn('created_at', function ($product) {
                return Carbon::parse($product->created_at)->format('d M Y');
            })
            ->editColumn('title', function ($product) use ($module) {
                return "<a  class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $product->id) . "' target='_blank'>$product->title</a>";
            })
            ->editColumn('status', function ($product) {
                return $this->renderStatusColumn($product);
            })
            ->rawColumns(['actions', 'status', 'title']) // Add 'status' to raw columns
            ->make(true);
    }

    protected function renderActionsColumn($product)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('PRODUCTS_SERVICES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['products_services'],
            'id' => $product->id
        ])->render();
    }

    protected function renderStatusColumn($product)
    {
        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('PRODUCTS_SERVICES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['products_services'],
            'id' => $product->id,
            'status' => [
                'current_status' => $product->status,
                'available_status' => CRM_STATUS_TYPES['PRODUCTS_SERVICES']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['PRODUCTS_SERVICES']['BT_CLASSES'],
            ]
        ])->render();
    }
}