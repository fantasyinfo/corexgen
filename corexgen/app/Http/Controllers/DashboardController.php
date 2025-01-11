<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Audit;
use App\Models\Company;
use App\Models\PaymentTransaction;
use App\Models\Plans;
use App\Models\Subscription;
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


    public function __construct(
        Company $companyModel,
        PaymentTransaction $paymentTransactionModel,
        Subscription $subscriptionModel,
        User $userModel,
        Plans $plansModel,
    ) {
        $this->companyModel = $companyModel;
        $this->paymentTransactionModel = $paymentTransactionModel;
        $this->subscriptionModel = $subscriptionModel;
        $this->userModel = $userModel;
        $this->plansModel = $plansModel;
    }

    public function companyHome()
    {
        return view($this->getViewFilePath('companyHome'), [
            'title' => 'Dashboard Management',
            'permissions' => PermissionsHelper::getPermissionsArray('DASHBOARD'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['dashboard'],
            'type' => 'Dashboard',
        ]);
    }

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
