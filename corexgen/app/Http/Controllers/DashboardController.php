<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Audit;
use App\Models\Calender;
use App\Models\CategoryGroupTag;
use App\Models\Company;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMContract;
use App\Models\CRM\CRMEstimate;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMProposals;
use App\Models\CRM\CRMRole;
use App\Models\CustomFieldDefinition;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Models\Plans;
use App\Models\ProductsServices;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\Tasks;
use App\Models\User;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    use TenantFilter;

    /**
     * Tenant-specific route prefix
     * @var string
     */
    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'dashboard.home.';

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


    protected $companyModel;
    protected $paymentTransactionModel;
    protected $subscriptionModel;
    protected $userModel;
    protected $plansModel;
    protected $projectModel;
    protected $invoiceModel;
    protected $tasksModel;
    protected $clientModelModel;


    public function __construct(
        Company $companyModel,
        PaymentTransaction $paymentTransactionModel,
        Subscription $subscriptionModel,
        User $userModel,
        Plans $plansModel,
        Project $projectModel,
        Invoice $invoiceModel,
        Tasks $tasksModel,
        CRMClients $clientModelModel,
    ) {
        $this->companyModel = $companyModel;
        $this->paymentTransactionModel = $paymentTransactionModel;
        $this->subscriptionModel = $subscriptionModel;
        $this->userModel = $userModel;
        $this->plansModel = $plansModel;
        $this->projectModel = $projectModel;
        $this->invoiceModel = $invoiceModel;
        $this->tasksModel = $tasksModel;
        $this->clientModelModel = $clientModelModel;
    }

    /**
     * company home dashboard
     */

    public function companyHome()
    {
        return view($this->getViewFilePath('companyHome'), [
            'title' => 'Dashboard Management',
            'permissions' => PermissionsHelper::getPermissionsArray('DASHBOARD'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['dashboard'],
            'type' => 'Dashboard',
            'activeProjects' => $this->projectModel->getActiveProjectsStats(),
            'projectsTimelines' => $this->projectModel->getProjectTimelineData(),
            'revenue' => $this->invoiceModel->getTotalRevenueStats(),
            'tasks' => $this->tasksModel->getActiveTasks(),
            'tasksCounts' => $this->tasksModel->getTasksCounts(),
            'clients' => $this->clientModelModel->getActiveClientsStats(),
            'recentActivities' => Audit::with('user')->latest()->limit(5)->get(),
            'recentInvoices' => $this->invoiceModel->getRecentInvoices(5)
        ]);
    }

    /**
     * super admin home dashboard
     */
    public function superHome()
    {

        return view($this->getViewFilePath('superHome'), [
            'title' => 'Dashboard Management',
            'permissions' => PermissionsHelper::getPermissionsArray('DASHBOARD'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['dashboard'],
            'type' => 'Dashboard',
            'totalCompanies' => $this->companyModel->totalCompany(),
            'monthlyRevenue' => $this->paymentTransactionModel->getMonthlyRevenue(),
            'activeSubscriptions' => $this->subscriptionModel->totalSubscriptions(),
            'totalUsers' => $this->userModel->totalUsers(null),
            'revenueData' => $this->paymentTransactionModel->getLastSixMonthsRevenue(),
            'planData' => $this->plansModel->getPlanDistribution(),
            'recentActivities' => Audit::with('user')->latest()->limit(5)->get()
        ]);
    }


    /**
     * global search super admin
     * @param \Illuminate\Http\Request $request
     */
    public function superSearch(Request $request)
    {
        $search = $request->input('q') ?? $request->input('mobile_q');

        // If no query, redirect to the dashboard with a message
        if (!$search) {
            return redirect()->route(getPanelRoutes('home'))->with('error', 'Please enter a search term.');
        }

        // companies
        $companies = Company::query()
            ->where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('phone', 'LIKE', "%{$search}%")
            ->latest()
            ->paginate(10);

        // users
        $users = User::query()
            ->where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->where('is_tenant', '=', "1")
            ->latest()
            ->paginate(10);

        // plans
        $plans = Plans::query()
            ->where('name', 'LIKE', "%{$search}%")
            ->latest()
            ->paginate(10);

        return view($this->getViewFilePath('searchSuper'), [
            'title' => 'Search',
            'type' => 'Search',
            'data' => [
                'companies' => [
                    'module' => 'companies',
                    'data' => $companies,
                    'title' => 'Companies List'
                ],
                'users' => [
                    'module' => 'users',
                    'data' => $users,
                    'title' => 'Users List'
                ],
                'plans' => [
                    'module' => 'plans',
                    'data' => $plans,
                    'title' => 'Plans List'
                ]
            ]
        ]);
    }


    /**
     * global search company
     * @param \Illuminate\Http\Request $request
     */
    public function companySearch(Request $request)
    {
        $search = $request->input('q') ?? $request->input('mobile_q');

        // Redirect to the dashboard if no search term is provided
        if (!$search) {
            return redirect()->route(getPanelRoutes('home'))->with('error', 'Please enter a search term.');
        }

        $companyId = Auth::user()->company_id;

        // Define search modules and their respective queries
        $modules = [
            'clients' => [
                'model' => CRMClients::query(),
                'fields' => ['company_name', 'first_name', 'middle_name', 'last_name', 'primary_email', 'primary_phone'],
                'title' => 'Clients List',
            ],
            'leads' => [
                'model' => CRMLeads::query()->with(['stage']),
                'fields' => ['company_name', 'title', 'first_name', 'last_name', 'email', 'phone'],
                'title' => 'Leads List',
            ],
            'proposals' => [
                'model' => CRMProposals::query()->with(['typable']),
                'fields' => ['title', '_id', '_prefix'],
                'title' => 'Proposals List',
            ],
            'estimates' => [
                'model' => CRMEstimate::query()->with(['typable']),
                'fields' => ['title', '_id', '_prefix'],
                'title' => 'Estimates List',
            ],
            'contracts' => [
                'model' => CRMContract::query()->with(['typable']),
                'fields' => ['title', '_id', '_prefix'],
                'title' => 'Contracts List',
            ],
            'products_services' => [
                'model' => ProductsServices::query(),
                'fields' => ['title', 'type'],
                'title' => 'Products List',
            ],
            'invoices' => [
                'model' => Invoice::query()->with(['client']),
                'fields' => ['_id', '_prefix'],
                'title' => 'Invoices List',
            ],
            'projects' => [
                'model' => Project::query(),
                'fields' => ['title', 'billing_type'],
                'title' => 'Projects List',
            ],
            'tasks' => [
                'model' => Tasks::query()->with(['stage','project']),
                'fields' => ['title', 'hourly_rate', 'priority'],
                'title' => 'Tasks List',
            ],
            'customfields' => [
                'model' => CustomFieldDefinition::query(),
                'fields' => ['field_label'],
                'title' => 'Custom Fields List',
            ],
            'calender' => [
                'model' => Calender::query(),
                'fields' => ['title', 'event_type', 'priority'],
                'title' => 'Calendar Events List',
            ],
            'users' => [
                'model' => User::query()->with(['role']),
                'fields' => ['name', 'email'],
                'title' => 'Users List',
            ],
            'role' => [
                'model' => CRMRole::query(),
                'fields' => ['role_name'],
                'title' => 'Roles List',
                'hideview' => true
            ],
            'category_tags' => [
                'model' => CategoryGroupTag::query(),
                'fields' => ['name'],
                'title' => 'Categories, Groups, and Tags List',
                'hideview' => true
            ],
        ];

        $data = [];
        foreach ($modules as $key => $module) {
            $query = $module['model'];
            $query->where('company_id', '=', $companyId)
                ->where(function ($q) use ($module, $search) {
                    foreach ($module['fields'] as $field) {
                        $q->orWhere($field, 'LIKE', "%{$search}%");
                    }
                });
            $data[$key] = [
                'module' => $key,
                'data' => $query->latest()->paginate(10),
                'title' => $module['title'],
            ];
        }

        return view($this->getViewFilePath('searchCompany'), [
            'title' => 'Search',
            'type' => 'Search',
            'data' => $data,
        ]);
    }






}
