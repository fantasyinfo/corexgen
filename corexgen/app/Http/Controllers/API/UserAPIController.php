<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CRM\CRMRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Jetstream;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
class UserAPIController extends Controller
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


        if ($users) {
            return response()->json([
                'message' => 'Users Fetched successful',
                'users' => filterUsersDetails($users),
                'roles' => filterRolesDetails($roles),
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],

            ], 200);
        } else {
            // Return an error response for failed login
            return response()->json([
                'message' => 'Users Not Found',
            ], 401);
        }

    }


    public function show($id)
    {
        try {
            $user = User::where('id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();

            $roles = CRMRole::where('buyer_id', auth()->user()->buyer_id)
                ->get();

            role:
            return response()->json([
                'message' => 'User found successfully',
                'data' => filerUserDetails($user),
                'roles' => filterRolesDetails($roles)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'max:255',
            ],
            'email' => 'required|email|string|max:1000',
            'password' => ['required', 'string'],
            'role_id' => 'required|integer'
        ]);


        // Handle form submission
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();
            $validated['buyer_id'] = auth()->user()->buyer_id;

            $validated['status'] = $validated['status'] ?? 'active';
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'message' => 'User created successfully',
                'data' => filerUserDetails($user)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the user',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request)
    {

   

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'max:255',
            ],
            'role_id' => 'required|integer'
        ]);

           // Handle form submission
           if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }



        try {
            $user = User::where('id', $request->id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();

 


            $validated = $validator->validated();
           

            $user->update($validated);



            return response()->json([
                'message' => 'User updated successfully',
                'data' => filerUserDetails($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the user',
                'error' => $e->getMessage()
            ], 500);
        }

    }


    public function toggleStatus($id)
    {
        try {
            // Delete the role
            $user = User::where('id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();


            $user->status = $user->status === 'active' ? 'deactive' : 'active';
            $user->save();
            // Return success response
            return response()->json([
                'message' => 'User status updated successfully',
                'data' => filerUserDetails($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the user status',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {

        try {
            // Delete the role
            $user = User::where('id', $id)
                ->where('buyer_id', auth()->user()->buyer_id)
                ->firstOrFail();

            $user->delete();
            // Return success response
            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'message' => 'An error occurred while deleting the user',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function login(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
            'buyer_id' => 'required|string',
        ]);

        // Use the User model instead of DB::table
        $user = User::join('buyers', 'buyers.id', '=', 'users.buyer_id')
            ->where('users.email', $validated['email'])
            ->where('users.status', 'active')
            ->where('buyers.buyer_id', $validated['buyer_id'])
            ->select('users.*')
            ->first();

        // Check if user exists and the password matches
        if ($user && Hash::check($validated['password'], $user->password)) {
            // Delete existing tokens if you want to allow only one active session
            $user->tokens()->delete();

            // Create a new token
            $token = $user->createToken($validated['device_name'] ?? 'auth_token')->plainTextToken;

            // Return a success response with a filtered user payload
            return response()->json([
                'message' => 'Login successful',
                'user' => $this->filterUserDetails($user, $validated['buyer_id']),
                'token' => $token,
            ], 200);
        }

        // Return an error response for failed login
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }



    protected function filterUserDetails($user, $buyerid)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'buyer_id' => $buyerid,
        ];
    }

    public function logout(Request $request)
    {

        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }


        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['email' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['email' => __($status)], 400);
    }
}
