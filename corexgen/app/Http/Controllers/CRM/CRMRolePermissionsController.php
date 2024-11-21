<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CRMPermissions;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMRolePermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CRMRolePermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $perPage = 10;



    public function index(Request $request)
    {


        $query = CRMRolePermissions::query()->where('buyer_id', auth()->user()->buyer_id);

        // Sorting


        // Pagination with additional parameters
        $permissions = $query->paginate($request->input('per_page', $this->perPage));

        // Add query parameters to pagination links
        $permissions->appends($request->all());


        $roles = CRMRole::where('buyer_id', auth()->user()->buyer_id)
            ->get();


        return view('dashboard.crm.permissions.index', [
            'permissions' => $permissions,
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
     * Display the specified resource.
     */
    public function show(CRMRolePermissions $cRMRolePermissions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CRMRolePermissions $cRMRolePermissions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CRMRolePermissions $cRMRolePermissions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CRMRolePermissions $cRMRolePermissions)
    {
        //
    }
}
