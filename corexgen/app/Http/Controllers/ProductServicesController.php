<?php

namespace App\Http\Controllers;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\ProposalEditRequest;
use App\Http\Requests\ProposalRequest;
use App\Http\Requests\UserRequest;
use App\Jobs\SendProposal;
use App\Models\Country;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMProposals;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMSettings;
use App\Models\CRM\CRMTemplates;
use App\Models\ProductsServices;
use App\Repositories\ProductServicesRepository;
use App\Services\CustomFieldService;
use App\Services\ProductServicesService;
use App\Services\UserService;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\IsSMTPValid;
use App\Traits\MediaTrait;
use App\Traits\SubscriptionUsageFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Media;
use App\Repositories\ProposalRepository;
use App\Services\ProposalService;
use Illuminate\Support\Str;

/**
 * UserController handles CRUD operations for Proposals
 * 
 * This controller manages user-related functionality including:
 * - Listing User with server-side DataTables
 * - Creating new User
 * - Editing existing User
 * - Exporting User to CSV
 * - Importing User from CSV
 * - Changing user here status removed,
 *  - New VErsion Check
 */

class ProductServicesController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;
    use MediaTrait;
    use IsSMTPValid;
    use CategoryGroupTagsFilter;

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
     * Display list of users with filtering and DataTables support
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


        // Fetch the totals in a single query

        // Build base query for user totals
        $user = Auth::user();
        $proposalQuery = ProductsServices::query();

        $proposalQuery = $this->applyTenantFilter($proposalQuery);

        // Get all totals in a single query
        $usersTotals = $proposalQuery->select([
            DB::raw('COUNT(*) as totalUsers'),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalActive',
                CRM_STATUS_TYPES['PRODUCTS_SERVICES']['STATUS']['ACTIVE']
            )),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalInactive',
                CRM_STATUS_TYPES['PRODUCTS_SERVICES']['STATUS']['DEACTIVE']
            ))
        ])->first();



        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES']]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $usersTotals->totalUsers,
            ];
        }


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Products Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PRODUCTS_SERVICES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['products_services'],
            'type' => 'Products & Services',
            'total_allow' => $usages['totalAllow'],
            'total_used' => $usages['currentUsage'],
            'total_active' => $usersTotals->totalActive,
            'total_inactive' => $usersTotals->totalInactive,
            'total_ussers' => $usersTotals->totalUsers,

        ]);
    }



    /**
     * Storing the data of user into db
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProposalRequest $request)
    {



        $this->tenantRoute = $this->getTenantRoute();

        try {


            $proposal = $this->proposalService->createProposal($request->validated());


            // update current usage
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES']]), '+', '1');



            return redirect()->route($this->tenantRoute . 'proposals.index')
                ->with('success', 'Proposals created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while creating the Proposals: ' . $e->getMessage());
        }
    }




    /**
     * Return the view for creating new user with roles
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {

        $this->checkCurrentUsage(strtolower(PLANS_FEATURES['PRODUCTS_SERVICES']));


        // categories
        $categoryQuery = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['products_categories'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services']);
        $categoryQuery = $this->applyTenantFilter($categoryQuery);
        $categories = $categoryQuery->get();


        // taxes
        $taxQuery = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['products_taxs'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services']);
        $taxQuery = $this->applyTenantFilter($taxQuery);
        // $taxes = $taxQuery->get();

        dd($taxQuery->toSql());

        $customFields = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['products'], Auth::user()->company_id);
        }



        return view($this->getViewFilePath('create'), [
            'title' => 'Create Product',
            'taxes' => $taxes,
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],
            'categories' => $categories,
            'customFields' => $customFields
        ]);
    }

    /**
     * showing the edit user view
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {

        $query = CRMProposals::query()
            ->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $proposal = $query->firstOrFail();


        // Regular view rendering
        $templates = $this->applyTenantFilter(CRMTemplates::query()->select('id', 'title')->where('type', 'Proposals'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();

        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Product',
            'proposal' => $proposal,
            'templates' => $templates,
            'clients' => $clients,
            'leads' => $leads,

        ]);
    }


    /**
     * Method update 
     * for updating the user
     *
     * @param Request $request [explicite description]
     *
     */
    public function update(ProposalEditRequest $request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        try {


            $proposal = $this->proposalService->updateProposal($request->validated());

            return redirect()->route($this->tenantRoute . 'proposals.index')
                ->with('success', 'proposal updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the proposal: ' . $e->getMessage());
        }
    }





    /**
     * Deleting the user
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        try {
            // Delete the user

            $user = $this->applyTenantFilter(CRMProposals::find($id));
            if ($user) {
                // delete  now
                $user->delete();

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
     * Bulk Delete the user
     * Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');


        try {
            // Delete the user

            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {



                    // Then delete the clients
                    CRMProposals::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected proposals deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No proposals selected for deletion.'], 400);

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the user: ' . $e->getMessage());
        }
    }




    public function view($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            CRMProposals::query()
                ->with([
                    'typable' => function ($query) {
                        if (request('typable_type') === CRMClients::class) {
                            // For Clients - many-to-many relationship
                            $query->with([
                                'addresses' => function ($addressQuery) {
                                $addressQuery->select(
                                    'addresses.id',
                                    'addresses.street_address',
                                    'addresses.postal_code',
                                    'addresses.city_id',
                                    'addresses.country_id'
                                )
                                    ->withPivot('type');
                            }
                            ]);
                        } elseif (request('typable_type') === CRMLeads::class) {
                            // For Leads - single address relationship
                            $query->with([
                                'address' => function ($addressQuery) {
                                $addressQuery->with(['country', 'city'])
                                    ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                            }
                            ]);
                        }
                    },
                    'template',
                    'company.addresses' => function ($query) {
                        $query->with(['country', 'city'])
                            ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                    }
                ])
                ->where('id', '=', $id)
        );



        $query = $this->applyTenantFilter($proposalQuery);
        $proposal = $query->firstOrFail();


        return view($this->getViewFilePath('view'), [
            'title' => 'View Product',
            'proposal' => $proposal,
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],

        ]);
    }







}
