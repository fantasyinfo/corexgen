<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\CRMRoleRequest;
use Illuminate\Http\Request;
use App\Models\CRM\CRMRole;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionsHelper;

/**
 * CRMRoleController handles CRUD operations for CRM Roles
 * 
 * This controller manages role-related functionality including:
 * - Listing roles with server-side DataTables
 * - Creating new roles
 * - Editing existing roles
 * - Exporting roles to CSV
 * - Importing roles from CSV
 * - Changing role status
 */
class CRMRoleController extends Controller
{
    use TenantFilter;

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
    private $viewDir = 'dashboard.crm.role.';

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
     * Display list of roles with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Initialize query with tenant filtering
        $query = CRMRole::query();
        $query = $this->applyTenantFilter($query);
        $this->tenantRoute = $this->getTenantRoute();

        // Apply dynamic filters based on request input
        $query->when($request->filled('name'), fn($q) => $q->where('role_name', 'LIKE', "%{$request->name}%"));
        $query->when($request->filled('status'), fn($q) => $q->where('status', $request->status));
        $query->when($request->filled('start_date'), fn($q) => $q->whereDate('created_at', '>=', $request->start_date));
        $query->when($request->filled('end_date'), fn($q) => $q->whereDate('created_at', '<=', $request->end_date));

        // Server-side DataTables response
        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('actions', function ($role) {
                return View::make(getComponentsDirFilePath('dt-actions-buttons'), [

                        'tenantRoute' => $this->tenantRoute,
                    'permissions' => PermissionsHelper::getPermissionsArray('ROLE'),
                    'module' => PANEL_MODULES['SUPER_PANEL']['role'],
                    'id' => $role->id

                    ])->render();
                })
                ->editColumn('created_at', fn($role) => $role->created_at->format('d M Y'))
                ->editColumn('status', function ($role) {
                return View::make(getComponentsDirFilePath('dt-status'), [

                    'tenantRoute' => $this->tenantRoute,
                    'permissions' => PermissionsHelper::getPermissionsArray('ROLE'),
                    'module' => PANEL_MODULES['SUPER_PANEL']['role'],
                    'id' => $role->id,
                    'status' => [
                        'current_status' => $role->status,
                        'available_status' => CRM_STATUS_TYPES['CRM_ROLES']['STATUS'],
                        'bt_class' => CRM_STATUS_TYPES['CRM_ROLES']['BT_CLASSES'],

                    ]
                    ])->render();
                })
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }

        // Render index view with filterss
        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Roles Management',
            'permissions' => PermissionsHelper::getPermissionsArray('ROLE'),
            'module' => PANEL_MODULES['SUPER_PANEL']['role'],
        ]);
    }

    /**
     * Store a newly created role
     * 
     * @param CRMRoleRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CRMRoleRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();
        try {
            // Validate and create role
            $validated = $request->validated();
            CRMRole::create($validated);

            // Redirect with success message
            return redirect()->route($this->tenantRoute . 'role.create')
                ->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role creation
            return redirect()->back()
                ->with('error', 'An error occurred while creating the role: ' . $e->getMessage());
        }
    }

    /**
     * Show create role form
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view($this->getViewFilePath('create'), [
            'title' => 'Create Role'
        ]);
    }

    /**
     * Show edit role form
     * 
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        // Apply tenant filtering to role query
        $query = CRMRole::query()->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $roleData = $query->firstOrFail();

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Role',
            'role' => $roleData
        ]);
    }

    /**
     * Update an existing role
     * 
     * @param CRMRoleRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(CRMRoleRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        try {
            // Validate and update role
            $validated = $request->validated();
            $query = CRMRole::query()->where('id', $request->id);
            $query = $this->applyTenantFilter($query);
            $query->update($validated);

            // Redirect with success message
            return redirect()->route($this->tenantRoute . 'role.index')
                ->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role update
            return redirect()->back()
                ->with('error', 'An error occurred while updating the role: ' . $e->getMessage());
        }
    }

    /**
     * Export roles to CSV
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // Initialize query with tenant filtering
        $query = CRMRole::query();
        $query = $this->applyTenantFilter($query);


        // Apply dynamic filters based on request input
        $query->when($request->filled('name'), fn($q) => $q->where('role_name', 'LIKE', "%{$request->name}%"));
        $query->when($request->filled('status'), fn($q) => $q->where('status', $request->status));
        $query->when($request->filled('start_date'), fn($q) => $q->whereDate('created_at', '>=', $request->start_date));
        $query->when($request->filled('end_date'), fn($q) => $q->whereDate('created_at', '<=', $request->end_date));

        // Get filtered roles
        $roles = $query->get();

        // Prepare CSV data
        $csvData = [];
        $csvData[] = ['ID', 'Role Name', 'Role Desc', 'Status', 'Created At', 'Updated At'];

        foreach ($roles as $role) {
            $csvData[] = [
                $role->id,
                $role->role_name,
                $role->role_desc,
                $role->status,
                $role->created_at->format('Y-m-d H:i:s'),
                $role->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        // Convert to CSV string
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        // Generate filename and return CSV file
        $fileName = 'roles_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$fileName}");
    }

    /**
     * Import roles from CSV
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        // Validate uploaded file
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $file = $request->file('file');
            $data = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($data);

        
            // Import each row from CSV
            foreach ($data as $row) {
                $row = array_combine($header, $row);

                CRMRole::create([
                    'role_name' => $row['role_name'] ?? '',
                    'role_desc' => $row['role_desc'] ?? '',
                    'status' => CRM_STATUS_TYPES['CRM_ROLES']['STATUS']['ACTIVE'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Roles imported successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a role
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            // Apply tenant filtering and delete role
            $query = CRMRole::query()->where('id', $id);
            $query = $this->applyTenantFilter($query);
            $query->delete();

            // Redirect with success message
            return redirect()->back()->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            // Handle any deletion errors
            return redirect()->back()->with('error', 'Failed to delete the role: ' . $e->getMessage());
        }
    }

    /**
     * Toggle role status between active and inactive
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatus($id, $status)
    {
        try {
            // Apply tenant filtering and find role
            $query = CRMRole::query()->where('id', $id);
            $query = $this->applyTenantFilter($query);
            $query->update(['status' => $status]);
            // Redirect with success message
            return redirect()->back()->with('success', 'Role status changed successfully.');
        } catch (\Exception $e) {
            // Handle any status change errors
            return redirect()->back()->with('error', 'Failed to change the role status: ' . $e->getMessage());
        }
    }
}
