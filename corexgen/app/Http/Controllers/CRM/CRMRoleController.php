<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\CRMRole;
use Illuminate\Validation\Rule;

class CRMRoleController extends Controller
{
    protected $perPage = 10;

    public function index(Request $request)
    {
        $query = CRMRole::query();

        // Advanced Filtering
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

        // Pagination with additional parameters
        $roles = $query->paginate($request->input('per_page', $this->perPage));

        // Add query parameters to pagination links
        $roles->appends($request->all());

        return view('dashboard.crm.role.index', [
            'roles' => $roles,
            'filters' => $request->all(),
            'title' => 'Roles Management'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => [
                'required',
                'max:255',
                Rule::unique('crm_roles')->where(function ($query) use ($request) {
                    return $query->where('buyer_id', $request->buyer_id);
                }),
            ],
            'role_desc' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);

        $role = CRMRole::create($validated);

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }


    public function create(){
        return view('dashboard.crm.role.create', [

            'title' => 'Create Role'
        ]);
    }
    public function update(Request $request, CRMRole $role)
    {
        $validated = $request->validate([
            'role_name' => [
                'required',
                'max:255',
                Rule::unique('crm_roles')
                    ->ignore($role->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('buyer_id', $request->buyer_id);
                    }),
            ],
            'role_desc' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);

        $role->update($validated);

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function export(Request $request)
    {
        // Implement export functionality based on current filters
        $query = CRMRole::query();
        // Apply same filters as in index method
        // Return Excel or CSV export
    }
}