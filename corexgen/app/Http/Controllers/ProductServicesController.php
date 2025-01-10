<?php

namespace App\Http\Controllers;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\ProductServicesEditRequest;
use App\Http\Requests\ProductServicesRequest;

use App\Models\ProductsServices;
use App\Repositories\ProductServicesRepository;
use App\Services\CustomFieldService;
use App\Services\ProductServicesService;
use App\Traits\CategoryGroupTagsFilter;

use App\Traits\StatusStatsFilter;
use App\Traits\SubscriptionUsageFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


/**
 * ProductServicesController handles CRUD operations for Products
 * 
 * This controller manages product-related functionality including:
 * - Listing product with server-side DataTables
 * - Creating new product
 * - Editing existing product
 * - Changing product here status removed,
 */

class ProductServicesController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;
    use CategoryGroupTagsFilter;
    use StatusStatsFilter;

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
    private $viewDir = 'dashboard.crm.productsservices.';

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


    protected $productSericeRepository;
    protected $productSercicesService;

    protected $customFieldService;

    public function __construct(
        ProductServicesRepository $productSericeRepository,
        ProductServicesService $productSercicesService,
        CustomFieldService $customFieldService


    ) {
        $this->productSericeRepository = $productSericeRepository;
        $this->productSercicesService = $productSercicesService;
        $this->customFieldService = $customFieldService;

    }

    /**
     * Display list of product with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Ajax DataTables request
        if ($request->ajax()) {
            return $this->productSercicesService->getDatatablesResponse($request);
        }


    
        $headerStatus = $this->getHeaderStatus(\App\Models\ProductsServices::class, PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES']);

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Products Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PRODUCTS_SERVICES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['products_services'],
            'type' => 'Products',
            'headerStatus' => $headerStatus,
            'taxes' =>  $this->productSercicesService->getProductTaxes(),
            'categories' => $this->productSercicesService->getProductCategories(),

        ]);
    }



    private function getHeaderStatus($model, $permission)
    {
        $user = Auth::user();

        // fetch totals status by clause
        $statusQuery = $this->getGroupByStatusQuery($model);
        $groupData = $this->applyTenantFilter($statusQuery['groupQuery'])->get()->toArray();
        $totalData = $this->applyTenantFilter($statusQuery['totalQuery'])->count();
        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[$permission]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $totalData,
            ];
        }

        return [
            'totalAllow' => $usages['totalAllow'],
            'currentUsage' => $totalData,
            'groupData' => $groupData
        ];
    }

    /**
     * Storing the data of product into db
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProductServicesRequest $request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['productsservices'], Auth::user()->company_id);
            }


            $product = $this->productSercicesService->createProduct($request->validated());


            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($product, $validatedData);
            }

            // update current usage
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES']]), '+', '1');



            return redirect()->route($this->tenantRoute . 'products_services.index')
                ->with('success', 'Products created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while creating the Products: ' . $e->getMessage());
        }
    }




    /**
     * Return the view for creating new product 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {

        $this->checkCurrentUsage(strtolower(PLANS_FEATURES['PRODUCTS_SERVICES']));

        // dd($taxQuery->toSql());

        $customFields = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['productsservices'], Auth::user()->company_id);
        }


        return view($this->getViewFilePath('create'), [
            'title' => 'Create Product',
            'module' => PANEL_MODULES[$this->getPanelModule()]['products_services'],
            'taxes' =>  $this->productSercicesService->getProductTaxes(),
            'categories' => $this->productSercicesService->getProductCategories(),
            'customFields' => $customFields
        ]);
    }

    /**
     * showing the edit product view
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {

        $query = ProductsServices::query()
            ->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $product = $query->firstOrFail();


        // Regular view rendering
       


        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['productsservices'], Auth::user()->company_id);

            $cfOldValues = $this->customFieldService->getValuesForEntity($product);
        }


        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Product',
            'product' => $product,
            'customFields' => $customFields,
            'cfOldValues' => $cfOldValues,
            'module' => PANEL_MODULES[$this->getPanelModule()]['products_services'],
            'taxes' =>  $this->productSercicesService->getProductTaxes(),
            'categories' => $this->productSercicesService->getProductCategories(),

        ]);
    }


    /**
     * Method update 
     * for updating the product
     *
     * @param Request $request [explicite description]
     *
     */
    public function update(ProductServicesEditRequest $request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        try {
            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['productsservices'], Auth::user()->company_id);
            }

            $product = $this->productSercicesService->updateProduct($request->validated());

            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($product, $validatedData);
            }

            return redirect()->route($this->tenantRoute . 'products_services.index')
                ->with('success', 'proposal updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the proposal: ' . $e->getMessage());
        }
    }





    /**
     * Deleting the product
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        try {
            // Delete the user

            $product = $this->applyTenantFilter(ProductsServices::find($id));
            if ($product) {
                // delete  now

                // delete its custom fields also if any
                $this->customFieldService->deleteEntityValues($product);


                $product->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES']]), '-', '1');

                return redirect()->back()->with('success', 'Product deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to delete the Product: Product not found with this id.');
            }

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the proposal: ' . $e->getMessage());
        }
    }






    /**
     * Bulk Delete the product
     * Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
    
        try {
            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {
                    $this->customFieldService->bulkDeleteEntityValues(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['productsservices'], $ids);
                    ProductsServices::whereIn('id', $ids)->delete();
    
                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES']]),
                        '-',
                        count($ids)
                    );
                });
    
                return response()->json(['message' => 'Selected products_services deleted successfully.'], 200);
            }
    
            return response()->json(['message' => 'No products_services selected for deletion.'], 400);
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Bulk delete failed: ' . $e->getMessage());
    
            return response()->json(['error' => 'Failed to delete the products_services: ' . $e->getMessage()], 500);
        }
    }
    


    /**
     * changing the status 
     * @param mixed $id
     * @param mixed $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatus($id, $status)
    {
        try {
            ProductsServices::query()->where('id', '=', $id)->update(['status' => $status]);
            // Return success response
            return redirect()->back()->with('success', 'Product status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the product status: ' . $e->getMessage());
        }
    }


    /**
     * view product
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function view($id)
    {
        $productQuery = $this->applyTenantFilter(
            ProductsServices::query()
                ->with([
                    'category',
                    'tax',
                    'createdBy',
                    'updatedBy'
                ])
                ->where('id', '=', $id)
        );

        $query = $this->applyTenantFilter($productQuery);
        $product = $query->firstOrFail();

        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['productsservices'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($product);
        }

        return view($this->getViewFilePath('view'), [
            'title' => 'View Product',
            'product' => $product,
            'module' => PANEL_MODULES[$this->getPanelModule()]['products_services'],
            'cfOldValues' => $cfOldValues,
            'customFields' => $customFields,

        ]);
    }



}
