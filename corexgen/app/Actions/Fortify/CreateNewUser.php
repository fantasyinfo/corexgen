<?php

namespace App\Actions\Fortify;

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\DB;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();
    
        try {
            DB::beginTransaction();
    
   
            // only for buyers/superadmin


           
                $buyerIdToMaintain = time();
                $buyer = Buyer::create([
                    'name' => $input['name'],
                    'email' => $input['email'],
                    'buyer_id' => $buyerIdToMaintain,
                    'password' => Hash::make($input['password']),
                ]);

                $userArr = [
                    'name' => $input['name'],
                    'email' => $input['email'],
                    'role_id' => 1, // superadmin 
                    'password' => Hash::make($input['password']),
                    'buyer_id' => $buyer->id
                ];
   
                $user = User::create($userArr);
                
            
        
    
            DB::commit();
    
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
