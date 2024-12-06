<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionsHelper;
use App\Traits\TenantFilter;

class UserService
{

    use TenantFilter;
    
    protected $userRepository;

    private $tenantRoute;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }

    public function getDatatablesResponse($request)
    {
        $query = $this->userRepository->getUsersQuery($request);
        $module = PANEL_MODULES[$this->getPanelModule()]['users'];

        return DataTables::of($query)
            ->addColumn('actions', function ($user) {
                return $this->renderActionsColumn($user);
            })
            ->editColumn('name', function ($user) use ($module) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $user->id) . "' target='_blank'>$user->name</a>";
            })
            ->editColumn('created_at', fn($user) => $user->created_at->format('d M Y'))
            ->editColumn('role_name', fn($user) => $user->role?->role_name ?? '')
            ->editColumn('status', function ($user) {
                return $this->renderStatusColumn($user);
            })
            ->rawColumns(['actions', 'status', 'role_name','name'])
            ->make(true);
    }

    protected function renderActionsColumn($user)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->getTenantRoute(),
            'permissions' => PermissionsHelper::getPermissionsArray('USERS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
            'id' => $user->id
        ])->render();
    }

    protected function renderStatusColumn($user)
    {
        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->getTenantRoute(),
            'permissions' => PermissionsHelper::getPermissionsArray('USERS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
            'id' => $user->id,
            'status' => [
                'current_status' => $user->status,
                'available_status' => CRM_STATUS_TYPES['USERS']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['USERS']['BT_CLASSES'],
            ]
        ])->render();
    }
}