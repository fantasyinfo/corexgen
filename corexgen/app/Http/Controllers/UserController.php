<?php

namespace App\Http\Controllers;

use App\Models\CRM\CRMRole;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $perPage = 10;



    public function index(Request $request)
    {

        $query = User::query()
            ->join('crm_roles', 'users.role_id', '=', 'crm_roles.id')
            ->where('users.buyer_id', auth()->user()->buyer_id)
            ->select('users.*', 'crm_roles.role_name');

        // Advanced Filtering
        $query->when($request->filled('name'), function ($q) use ($request) {
            $q->where('users.name', 'LIKE', "%{$request->name}%");
        });

        $query->when($request->filled('email'), function ($q) use ($request) {
            $q->where('users.email', 'LIKE', "%{$request->name}%");
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('users.status', $request->status);
        });

        $query->when($request->filled('role_id'), function ($q) use ($request) {
            $q->where('users.role_id', $request->role_id);
        });

        $query->when($request->filled('start_date'), function ($q) use ($request) {
            $q->whereDate('users.created_at', '>=', $request->start_date);
        });

        $query->when($request->filled('end_date'), function ($q) use ($request) {
            $q->whereDate('users.created_at', '<=', $request->end_date);
        });

        // Sorting
        $sortField = $request->input('sort', 'users.created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination with additional parameters
        $users = $query->paginate($request->input('per_page', $this->perPage));

        // Add query parameters to pagination links
        $users->appends($request->all());

        $roles = CRMRole::where('buyer_id', auth()->user()->buyer_id)
            ->get();

        return view('dashboard.crm.users.index', [
            'users' => $users,
            'filters' => $request->all(),
            'title' => 'Users Management',
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'max:255',
            ],
            'email' => 'required|string|max:1000',
            'password' => ['required', 'string'],
            'role_id' => 'required|integer'
        ]);


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

            $validated['status'] = $validated['status'] ?? 'active';
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return redirect()->route('crm.users.create')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while creating the user: ' . $e->getMessage());
        }
    }




    public function create()
    {
        $roles = CRMRole::where('buyer_id', auth()->user()->buyer_id)
            ->get();

        return view('dashboard.crm.users.create', [

            'title' => 'Create User',
            'roles' => $roles
        ]);
    }

    public function edit($id)
    {
        $user = User::where('id', $id)
            ->where('buyer_id', auth()->user()->buyer_id)
            ->firstOrFail();

        $roles = CRMRole::where('buyer_id', auth()->user()->buyer_id)
            ->get();


        return view('dashboard.crm.users.edit', [

            'title' => 'Edit User',
            'user' => $user,
            'roles' => $roles
        ]);
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'max:255',
            ],
            'email' => 'required|string|max:1000',
            'role_id' => 'required|integer'
        ]);


        $role = User::where('id', $request->id)
            ->where('buyer_id', auth()->user()->buyer_id)
            ->firstOrFail();


        $validated = $validator->validated();
        $role->update($validated);

        return redirect()->route('crm.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function export(Request $request)
    {
        $query = User::query()
            ->join('crm_roles', 'users.role_id', '=', 'crm_roles.id')
            ->where('users.buyer_id', auth()->user()->buyer_id)
            ->select('users.*', 'crm_roles.role_name');

        // Apply filters as in index method
        $query->when($request->filled('name'), function ($q) use ($request) {
            $q->where('users.name', 'LIKE', "%{$request->name}%");
        });


        $query->when($request->filled('email'), function ($q) use ($request) {
            $q->where('users.email', 'LIKE', "%{$request->name}%");
        });


        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('users.status', $request->status);
        });

        $query->when($request->filled('role_id'), function ($q) use ($request) {
            $q->where('users.role_id', $request->role_id);
        });


        $query->when($request->filled('start_date'), function ($q) use ($request) {
            $q->whereDate('users.created_at', '>=', $request->start_date);
        });

        $query->when($request->filled('end_date'), function ($q) use ($request) {
            $q->whereDate('users.created_at', '<=', $request->end_date);
        });

        // Get the filtered data
        $roles = $query->get();

        // Generate CSV content
        $csvData = [];
        $csvData[] = ['ID', 'Full Name', 'Email', 'Role', 'Status', 'Created At', 'Updated At']; // CSV headers

        foreach ($roles as $role) {
            $csvData[] = [
                $role->id,
                $role->name,
                $role->email,
                $role->role_name,
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
        $fileName = 'users_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
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

                // Skip if email already exists
                if (User::where('email', $row['email'])->exists()) {
                    continue;
                }

                // Check if role_id exists in crm_roles table
                $roleExists = $row['role_id'] && DB::table('crm_roles')->where('id', $row['role_id'])->exists();

                if (!$roleExists) {
                    // Skip this row if the role doesn't exist
                    continue;
                }

                User::create([
                    'name' => $row['name'] ?? '',
                    'email' => $row['email'] ?? '',
                    'password' => Hash::make($row['password']) ?? '',
                    'role_id' => $row['role_id'] ?? '',
                    'buyer_id' => auth()->user()->buyer_id,

                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Users imported successfully!',
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
            $role = User::where('id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();

            $role->delete();
            // Return success response
            return redirect()->back()->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the user: ' . $e->getMessage());
        }
    }


    public function changeStatus($id)
    {
        try {
            // Delete the role
            $user = User::where('id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();


            $user->status = $user->status === 'active' ? 'deactive' : 'active';
            $user->save();
            // Return success response
            return redirect()->back()->with('success', 'User status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the user status: ' . $e->getMessage());
        }
    }
}
