<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('ROLE.CREATE')) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = Auth::user()->company_id ?? null; // Handle null cases for non-authenticated users
        $userId = $this->input('id') ?? null; // Safely retrieve user ID for update validation
        $isProfile = $this->input('is_profile') ?? false; // Safely retrieve user ID for update validation
    
        return [
            'id' => [$this->isMethod('put') || $this->isMethod('patch') ? 'required' : 'nullable'],
            'name' => [
                'required',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'string',
                'max:1000',
                Rule::unique('users')
                    ->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    })
                    ->ignore($userId), // Ignore current user for updates
            ],
            'password' => $this->isMethod('post') // Required for create, nullable for update
                ? ['required', 'string']
                : ['nullable', 'string'],
            'role_id' => [$isProfile ? 'nullable' : 'required', 'integer'],
            'address_street_address' => 'nullable|string|max:255',
            'address_country_id' => 'nullable',
            'address_city_id' => 'nullable',
            'address_pincode' => 'nullable|string|max:10',
        ];
    }
    
}
