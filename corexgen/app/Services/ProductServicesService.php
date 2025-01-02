<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\ProductsServices;
use App\Repositories\ProductServicesRepository;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\TenantFilter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class ProductServicesService
{
    use TenantFilter;
    use CategoryGroupTagsFilter;


    protected $productSericeRepository;

    private $tenantRoute;

    public function __construct(ProductServicesRepository $productSericeRepository)
    {
        $this->productSericeRepository = $productSericeRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }


    public function createProduct($data)
    {

        if (isset($validatedData['cgt_id'])) {
            $validCGTID = $this->checkIsValidCGTID($data['cgt_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['products_categories'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services']);


            if (!$validCGTID) {
                throw new \InvalidArgumentException("Failed to create product beacuse invalid CGT ID ");
            }
        }

        if (isset($validatedData['tax_id'])) {
            $validTaxID = $this->checkIsValidCGTID($data['tax_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['products_taxs'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services']);


            if (!$validTaxID) {
                throw new \InvalidArgumentException("Failed to create product beacuse invalid TAX ID ");
            }
        }

        return ProductsServices::create($data);
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
            ->editColumn('rate', function ($product) {
                return number_format($product->rate);
            })
            ->editColumn('category', function ($product) {
                return  $product?->category?->name ? "<span class='badge badge-pill bg-" . $product?->category?->color . "'>{$product?->category?->name}</span>" : 'N/A';
            })
            ->editColumn('tax', function ($product) {
                return $product?->tax?->name ? "<span class='badge badge-pill bg-" . $product?->tax?->color . "'>{$product?->tax?->name}</span>": 'N/A';
            })
            ->editColumn('title', function ($product) use ($module) {
                return "<a  class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $product->id) . "' target='_blank'>$product->title</a>";
            })
            ->editColumn('status', function ($product) {
                return $this->renderStatusColumn($product);
            })
            ->rawColumns(['actions','category','tax', 'status', 'title']) // Add 'status' to raw columns
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
