<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Audit;
use App\Models\Company;
use App\Models\CRM\CRMClients;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Models\Plans;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\Tasks;
use App\Models\User;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;

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
}
