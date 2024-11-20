<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CRMRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CRMRoleAPIController extends Controller
{
    //
    protected $perPage = 10;

    public function index(Request $request)
    {
        $query = CRMRole::query()->where('buyer_id', auth()->user()->buyer_id);

        // Apply filters
        $query->when($request->filled('name'), function ($q) use ($request) {
            $q->where('role_name', 'LIKE', "%{$request->name}%");
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $query->when($request->filled('start_date'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->start_date);
        });

        $query->when($request->filled('end_date'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->end_date);
        });

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $roles = $query->paginate($request->input('per_page', $this->perPage));

        if ($roles) {
            return response()->json([
                'message' => 'Roles Fetched successful',
                'roles' => filterRolesDetails($roles),
                'pagination' => [
                    'total' => $roles->total(),
                    'per_page' => $roles->perPage(),
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                    'from' => $roles->firstItem(),
                    'to' => $roles->lastItem(),
                ],

            ], 200);
        } else {
            // Return an error response for failed login
            return response()->json([
                'message' => 'Roles Not Found',
            ], 401);
        }
    }



    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'role_name' => 'required|string|max:255',
                'role_desc' => 'string|max:255',
                'status' => 'nullable|string|in:active,inactive',
                // Add other fields as needed
            ]);

            $validated['buyer_id'] = auth()->user()->buyer_id;
            $validated['created_by'] = auth()->user()->id;
            $validated['updated_by'] = auth()->user()->id;
            $validated['status'] = $validated['status'] ?? 'active';

            $role = CRMRole::create($validated);

            return response()->json([
                'message' => 'Role created successfully',
                'data' => filterRoleDetails($role)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the role',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $role = CRMRole::where('id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();

            role:
            return response()->json([
                'message' => 'Role found successfully',
                'data' =>filterRoleDetails($role)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Role not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $role = CRMRole::where('id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();

            $validated = $request->validate([
                'role_name' => 'required|string|max:255',
                'role_desc' => 'string|max:255',
                // Add other fields as needed
            ]);

            $validated['updated_by'] = auth()->user()->id;

            $role->update($validated);

            return response()->json([
                'message' => 'Role updated successfully',
                'data' => filterRoleDetails($role)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the role',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $role = CRMRole::where('id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();

            $role->delete();

            return response()->json([
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the role',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function toggleStatus($id)
    {
        try {
            $role = CRMRole::where('id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();

            $role->updated_by = auth()->user()->id;
            $role->status = $role->status === 'active' ? 'deactive' : 'active';
            $role->save();

            return response()->json([
                'message' => 'Role status changed successfully',
                'data' => filterRoleDetails($role)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while changing the role status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
