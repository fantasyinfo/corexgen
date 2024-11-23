<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\CRMRole;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class CRMRoleController extends Controller
{
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

        // Server-side DataTables response
        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('actions', function ($role) {
                    $editButton = '';
                    $deleteButton = '';

                    if (hasPermission('ROLE.UPDATE')) {
                        $editButton = '<a href="' . route('crm.role.edit', $role->id) . '" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Edit">
                               <i class="fas fa-pencil-alt"></i>
                           </a>';
                    }

                    if (hasPermission('ROLE.DELETE')) {
                        $deleteButton = '<form action="' . route('crm.role.destroy', $role->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure?\');">
                                 ' . csrf_field() . method_field('DELETE') . '
                                 <button type="submit" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Delete">
                                     <i class="fas fa-trash-alt"></i>
                                 </button>
                             </form>';
                    }

                    return "<div class='text-end'> $editButton  $deleteButton </div>";
                })
                ->editColumn('created_at', function ($role) {
                    return $role->created_at->format('d M Y');
                })
                ->editColumn('status', function ($role) {
                    if (hasPermission('ROLE.CHANGE_STATUS')) {
                        return "<a 
                        data-toggle='tooltip' 
                        data-placement='top' 
                        title='" . ($role->status == 'active' ? 'De Active' : 'Active') . "' 
                        href='" . route('crm.role.changeStatus', ['id' => $role->id]) . "'>
                            <span class='badge " . ($role->status == 'active' ? 'bg-success' : 'bg-danger') . "'>
                                " . ucfirst($role->status) . "
                            </span>
                        </a>";
                    }

                    return "<span class='badge " . ($role->status == 'active' ? 'bg-success' : 'bg-danger') . "'>
                        " . ucfirst($role->status) . "
                    </span>";
                })
                ->rawColumns(['actions', 'status']) // Add 'status' to raw columns
                ->make(true);
        }

        return view('dashboard.crm.role.index', [
            'filters' => $request->all(),
            'title' => 'Roles Management'
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => [
                'required',
                'max:255',
                Rule::unique('crm_roles')->where(function ($query) use ($request) {
                    return $query->where('buyer_id', auth()->user()->buyer_id ?? null);
                }),
            ],
            'role_desc' => 'nullable|string|max:1000',
            'status' => 'nullable|in:active,inactive'
        ], [
            'role_name.unique' => 'This role name already exists for your organization.',
            'role_name.required' => 'Role name is required.',
            'role_desc.max' => 'Role description cannot exceed 1000 characters.'
        ]);

        // Handle ajax validation requests
        if ($request->ajax()) {
            return response()->json([
                'valid' => $validator->passes(),
                'errors' => $validator->errors()
            ]);
        }

        // Handle form submission
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the form errors.');
        }

        try {
            $validated = $validator->validated();
            $validated['buyer_id'] = auth()->user()->buyer_id;
            $validated['created_by'] = auth()->user()->id;
            $validated['updated_by'] = auth()->user()->id;
            $validated['status'] = $validated['status'] ?? 'active';

            $role = CRMRole::create($validated);

            return redirect()->route('crm.role.create')
                ->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while creating the role: ' . $e->getMessage());
        }
    }

    public function validateField(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $validator = Validator::make(
            [$field => $value],
            [
                'role_name' => [
                    'required',
                    'max:255',
                    Rule::unique('crm_roles')->where(function ($query) use ($value) {
                        return $query->where('buyer_id', auth()->user()->buyer_id ?? null);
                    }),
                ],
                'role_desc' => 'nullable|string|max:1000'
            ]
        );

        return response()->json([
            'valid' => $validator->passes(),
            'errors' => $validator->errors()->get($field)
        ]);
    }


    public function create()
    {
        return view('dashboard.crm.role.create', [

            'title' => 'Create Role'
        ]);
    }

    public function edit($id)
    {
        $roleData = CRMRole::where('id', $id)
        ->where('buyer_id', auth()->user()->buyer_id)
            ->firstOrFail();
        return view('dashboard.crm.role.edit', [

            'title' => 'Edit Role',
            'role' => $roleData
        ]);
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'role_name' => [
                'required',
                'max:255',
                Rule::unique('crm_roles')
                    ->ignore($request->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('buyer_id', $request->buyer_id);
                    }),
            ],
            'role_desc' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);


        $role = CRMRole::where('id', $request->id)
            ->where('buyer_id', auth()->user()->buyer_id)
            ->firstOrFail();

        $validated['updated_by'] = auth()->user()->id;

        $role->update($validated);

        return redirect()->route('crm.role.index')
            ->with('success', 'Role updated successfully.');
    }

    public function export(Request $request)
    {
        $query = CRMRole::query()->where('buyer_id', auth()->user()->buyer_id);

        // Apply filters as in index method
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

        // Get the filtered data
        $roles = $query->get();

        // Generate CSV content
        $csvData = [];
        $csvData[] = ['ID', 'Role Name', 'Role Desc', 'Status', 'Created At', 'Updated At']; // CSV headers

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

        // Convert the data to CSV string
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        // Return the response with the CSV content as a file
        $fileName = 'roles_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$fileName}");
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:2048', // Validate file type and size
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

            foreach ($data as $row) {
                $row = array_combine($header, $row);

                CRMRole::create([
                    'role_name' => $row['role_name'] ?? '',
                    'role_desc' => $row['role_desc'] ?? '',
                    'status' => $row['status'] ?? 'active',
                    'buyer_id' => auth()->user()->buyer_id,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
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


    public function destroy($id)
    {

        try {
            // Delete the role
            $role = CRMRole::where('id', $id)
            ->where('buyer_id', auth()->user()->buyer_id)
            ->firstOrFail();

            $role->delete();
            // Return success response
            return redirect()->back()->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the role: ' . $e->getMessage());
        }
    }


    public function changeStatus($id)
    {
        try {
            // Delete the role
            $role = CRMRole::where('id', $id)
            ->where('buyer_id', auth()->user()->buyer_id)
            ->firstOrFail();

            $role->updated_by = auth()->user()->id;
            $role->status = $role->status === 'active' ? 'deactive' : 'active';
            $role->save();
            // Return success response
            return redirect()->back()->with('success', 'Role status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the role status: ' . $e->getMessage());
        }
    }
}
