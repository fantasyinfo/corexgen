<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolePermissionsRequest;
use App\Models\CRM\CRMPermissions;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMRolePermissions;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionsHelper;
use Illuminate\Support\Facades\Auth;

/**
 * CRMRolePermissionsController handles CRUD operations for CRM Roles Permissions
 * 
 * This controller manages role-related functionality including:
 * - Listing permissions with server-side DataTables
 * - Creating new permissions
 * - Editing existing permissions
 * - Deleting the existing permissions
 */
class RolePermissionsController extends Controller
{
    use TenantFilter;
    /**
     * Display a listing of the resource.
     */

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
    private $viewDir = 'dashboard.permissions.';

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

    /**
     * Display list of permissions with filtering and DataTables support
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {


        $query = CRMRolePermissions::query()
            ->join('crm_roles', 'crm_role_permissions.role_id', '=', 'crm_roles.id')
            ->join('crm_permissions', 'crm_role_permissions.permission_id', '=', 'crm_permissions.permission_id')
            ->groupBy('crm_roles.id', 'crm_roles.role_name')
            ->select('crm_roles.id', 'crm_roles.role_name');

        $query = $this->applyTenantFilter($query, 'crm_role_permissions');
        $this->tenantRoute = $this->getTenantRoute();

        // dd( $query->toSql());




        if ($request->ajax()) {
            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $searchValue = $request->input('search')['value']) {
                        $query->where(function ($query) use ($searchValue) {
                            $query->orWhere('crm_roles.role_name', 'like', "%$searchValue%");
                            // Add more columns as needed
                            // $query->orWhere('another_column', 'like', "%$searchValue%");
                        });
                    }
                })
                ->addColumn('actions', function ($permission) {
                    return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
                        'tenantRoute' => $this->tenantRoute,
                        'permissions' => PermissionsHelper::getPermissionsArray('PERMISSIONS'),
                        'module' => PANEL_MODULES[$this->getPanelModule()]['permissions'],
                        'id' => $permission->id,
                    ])->render();
                })
                ->rawColumns(['actions'])
                ->make(true);
        }


        $roleQuery = CRMRole::query();
        $roleQuery = $this->applyTenantFilter($roleQuery);
        $roles = $roleQuery->get();


        return view($this->getViewFilePath('index'), [

            'filters' => $request->all(),
            'roles' => $roles,
            'title' => 'Permissions Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PERMISSIONS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['permissions'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // roles
        $roleQuery = CRMRole::query();
        $roleQuery = $this->applyTenantFilter($roleQuery);
        $roles = $roleQuery->get();

        // In your controller method
        if (Auth::user()->is_tenant) {
            $crm_p_query = CRMPermissions::query();
        } else if (Auth::user()->company_id != null) {
            $crm_p_query = CRMPermissions::query()
            ->leftJoin('crm_role_permissions', 'crm_permissions.permission_id', '=', 'crm_role_permissions.permission_id')
            ->select('crm_permissions.*')
            ->where(function ($query) {
                $query->where('crm_permissions.parent_menu', '1')
                    ->orWhereNotNull('crm_permissions.parent_menu_id');
            })
            ->distinct(); // 

            $crm_p_query = $this->applyTenantFilter($crm_p_query, 'crm_role_permissions');
        }

        $crm_permissions = $crm_p_query->get();

        //  dd($crm_permissions);


        return view($this->getViewFilePath('create'), [

            'title' => 'Create Permission',
            'roles' => $roles,
            'crm_permissions' => $crm_permissions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RolePermissionsRequest $request)
    {

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Validated data
            $validated = $request->validated();
            $companyId = Auth::user()->company_id;
            $roleId = $validated['role_id'];

            // Remove any duplicate or invalid permission entries
            $permissions = array_unique(array_filter($validated['permissions']));

            // Throw an exception if no valid permissions are found
            if (empty($permissions)) {
                throw new \Exception('No valid permissions selected.');
            }

            // Bulk insert permissions
            $permissionsToInsert = collect($permissions)->map(function ($permissionId) use ($companyId, $roleId) {
                return [
                    'company_id' => $companyId,
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            })->toArray();

            // Delete existing permissions for this role before inserting new ones
            $query = CRMRolePermissions::query();
            $query = $this->applyTenantFilter($query);
            $query = $query->where('role_id', '=', $roleId);
            $query->delete();


            // Bulk insert new permissions
            CRMRolePermissions::insert($permissionsToInsert);

            // Commit the transaction
            DB::commit();

            $this->tenantRoute = $this->getTenantRoute();

            // Redirect with success message
            return redirect()->route($this->tenantRoute . 'permissions.index')
                ->with('success', 'Permissions created successfully.');
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();

            // Log the error
            \Log::error('Permission Creation Error: ' . $e->getMessage());

            // Redirect back with error message
            return redirect()->back()
                ->with('error', 'An error occurred while creating the permissions: ' . $e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Find the role to be edited
        $query = CRMRole::query()->where('id', '=', $id); // Use $id without quotes
        $query = $this->applyTenantFilter($query);
        $role = $query->firstOrFail(); // Corrected usage

        // Get the existing permissions for this role
        $queryRolePermission = CRMRolePermissions::query()->where('role_id', '=', $id);
        $queryRolePermission = $this->applyTenantFilter($queryRolePermission);
        $existingPermissions = $queryRolePermission->pluck('permission_id')->toArray();

        // Get all roles for the dropdown
        $roles = $this->applyTenantFilter(CRMRole::query())->get();

        // Get all permissions
        // In your controller method
        if (Auth::user()->is_tenant) {
            $crm_p_query = CRMPermissions::query();
        } else if (Auth::user()->company_id != null) {
            $crm_p_query = CRMPermissions::query()
                ->leftJoin('crm_role_permissions', 'crm_permissions.permission_id', '=', 'crm_role_permissions.permission_id')
                ->select('crm_permissions.*')
                ->where(function ($query) {
                    $query->where('crm_permissions.parent_menu', '1')
                        ->orWhereNotNull('crm_permissions.parent_menu_id');
                })->distinct();


            $crm_p_query = $this->applyTenantFilter($crm_p_query, 'crm_role_permissions');
        }

        $crm_permissions = $crm_p_query->get();

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Permissions',
            'role' => $role, // Pass the specific role being edited
            'existingPermissions' => $existingPermissions, // Pass existing permissions
            'roles' => $roles,
            'crm_permissions' => $crm_permissions,
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(RolePermissionsRequest $request)
    {

        // Start a database transaction
        DB::beginTransaction();
        $this->tenantRoute = $this->getTenantRoute();
        try {
            // Validated data
            $validated = $request->validated();
            $companyId = Auth::user()->company_id;
            $roleId = $validated['role_id'];

            // Remove any duplicate or invalid permission entries
            $permissions = isset($validated['permissions'])
                ? array_unique(array_filter($validated['permissions']))
                : [];

            // Delete existing permissions for this role before inserting new ones
            $this->applyTenantFilter(CRMRolePermissions::query()->where('role_id', '=', $roleId))->delete();

            // If permissions are provided, insert them
            if (!empty($permissions)) {
                $permissionsToInsert = collect($permissions)->map(function ($permissionId) use ($companyId, $roleId) {
                    return [
                        'company_id' => $companyId,
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                })->toArray();

                // Bulk insert new permissions
                CRMRolePermissions::insert($permissionsToInsert);
            }

            // Commit the transaction
            DB::commit();

            // Redirect with success message
            return redirect()->route($this->tenantRoute . 'permissions.index')
                ->with('success', 'Permissions updated successfully.');
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();

            // Log the error
            \Log::error('Permission Update Error: ' . $e->getMessage());

            // Redirect back with error message
            return redirect()->back()
                ->with('error', 'An error occurred while updating the permissions: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Delete all rows with matching role_id and buyer_id

            $this->applyTenantFilter(CRMRolePermissions::query()->where('role_id', '=', $id))->delete();

            // Return success response
            return redirect()->back()->with('success', 'Permissions deleted successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the Permissions: ' . $e->getMessage());
        }
    }
}
