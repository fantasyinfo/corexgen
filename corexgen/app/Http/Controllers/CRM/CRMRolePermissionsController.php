<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CRMPermissions;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMRolePermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CRMRolePermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $perPage = 10;



    public function index(Request $request)
    {




        $query = CRMRolePermissions::query()
            ->join('crm_roles', 'crm_role_permissions.role_id', '=', 'crm_roles.id')
            ->join('crm_permissions', 'crm_role_permissions.permission_id', '=', 'crm_permissions.permission_id')
            ->where('crm_role_permissions.buyer_id', auth()->user()->buyer_id)
            ->groupBy('crm_roles.id', 'crm_roles.role_name')
            ->select('crm_roles.id', 'crm_roles.role_name');



        if ($request->ajax()) {
            return DataTables::of($query)
            ->addColumn('actions', function ($permissions) {
                $editButton = '';
                $deleteButton = '';

                if (hasPermission('PERMISSIONS.UPDATE')) {
                    $editButton = '<a href="' . route('crm.permissions.edit', $permissions->id) . '" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Edit">
                               <i class="fas fa-pencil-alt"></i>
                           </a>';
                }

                if (hasPermission('PERMISSIONS.DELETE')) {
                    $deleteButton = '<form action="' . route('crm.permissions.destroy', $permissions->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure?\');">
                                 ' . csrf_field() . method_field('DELETE') . '
                                 <button type="submit" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Delete">
                                     <i class="fas fa-trash-alt"></i>
                                 </button>
                             </form>';
                }

                    return "<div class='text-end'> $editButton  $deleteButton </div>";
                })
                ->rawColumns(['actions', 'status']) // Add 'status' to raw columns
                ->make(true);
        }


        $roles = CRMRole::where('buyer_id', auth()->user()->buyer_id)
            ->get();


        return view('dashboard.crm.permissions.index', [
          
            'filters' => $request->all(),
            'roles' => $roles,
            'title' => 'Permissions Management'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $roles = CRMRole::where('buyer_id', auth()->user()->buyer_id)
            ->get();

        $crm_permissions = CRMPermissions::where('buyer_id', 1)
            ->get();

        return view('dashboard.crm.permissions.create', [

            'title' => 'Create Permission',
            'roles' => $roles,
            'crm_permissions' => $crm_permissions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'role_id' => ['required', 'exists:crm_roles,id'], // Add exists validation
            'permissions' => ['required', 'array'], // Ensure permissions is an array
        ]);

        // Handle validation failures
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the form errors.');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Validated data
            $validated = $validator->validated();
            $buyerId = auth()->user()->buyer_id;
            $roleId = $validated['role_id'];

            // Remove any duplicate or invalid permission entries
            $permissions = array_unique(array_filter($validated['permissions']));

            // Throw an exception if no valid permissions are found
            if (empty($permissions)) {
                throw new \Exception('No valid permissions selected.');
            }

            // Bulk insert permissions
            $permissionsToInsert = collect($permissions)->map(function ($permissionId) use ($buyerId, $roleId) {
                return [
                    'buyer_id' => $buyerId,
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            })->toArray();

            // Delete existing permissions for this role before inserting new ones
            CRMRolePermissions::where('role_id', $roleId)
                ->where('buyer_id', $buyerId)
                ->delete();

            // Bulk insert new permissions
            CRMRolePermissions::insert($permissionsToInsert);

            // Commit the transaction
            DB::commit();

            // Redirect with success message
            return redirect()->route('crm.permissions.create')
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
        $role = CRMRole::where('id', $id)
            ->where('buyer_id', auth()->user()->buyer_id)
            ->firstOrFail();

        // Get the existing permissions for this role
        $existingPermissions = CRMRolePermissions::where('role_id', $id)
            ->where('buyer_id', auth()->user()->buyer_id)
            ->pluck('permission_id')
            ->toArray();

        // Get all roles for the dropdown
        $roles = CRMRole::where('buyer_id', auth()->user()->buyer_id)
            ->get();

        // Get all permissions
        $crm_permissions = CRMPermissions::where('buyer_id', 1)
            ->get();

        return view('dashboard.crm.permissions.edit', [
            'title' => 'Edit Permissions',
            'role' => $role, // Pass the specific role being edited
            'existingPermissions' => $existingPermissions, // Pass existing permissions
            'roles' => $roles,
            'crm_permissions' => $crm_permissions
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
     
        // Validate the request
        $validator = Validator::make($request->all(), [
            'role_id' => ['required', 'exists:crm_roles,id'],
            'permissions' => ['sometimes', 'array'],
        ]);

        // Handle validation failures
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the form errors.');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Validated data
            $validated = $validator->validated();
            $buyerId = auth()->user()->buyer_id;
            $roleId = $validated['role_id'];

            // Remove any duplicate or invalid permission entries
            $permissions = isset($validated['permissions'])
                ? array_unique(array_filter($validated['permissions']))
                : [];

            // Delete existing permissions for this role before inserting new ones
            CRMRolePermissions::where('role_id', $roleId)
                ->where('buyer_id', $buyerId)
                ->delete();

            // If permissions are provided, insert them
            if (!empty($permissions)) {
                $permissionsToInsert = collect($permissions)->map(function ($permissionId) use ($buyerId, $roleId) {
                    return [
                        'buyer_id' => $buyerId,
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
            return redirect()->route('crm.permissions.index')
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
            CRMRolePermissions::where('role_id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->delete();

            // Return success response
            return redirect()->back()->with('success', 'Permissions deleted successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the Permissions: ' . $e->getMessage());
        }
    }
}
