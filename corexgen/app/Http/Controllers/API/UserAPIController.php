<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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

class UserAPIController extends Controller
{


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
                'user' => $this->filterUserDetails($user,$validated['buyer_id']),
                'token' => $token,
            ], 200);
        }
    
        // Return an error response for failed login
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }



    protected function filterUserDetails($user,$buyerid)
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
