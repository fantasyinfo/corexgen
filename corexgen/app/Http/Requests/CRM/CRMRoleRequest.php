<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CRMRoleRequest extends FormRequest
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
        $companyId = Auth::user()->company_id; // Get the company_id from the authenticated user
        $roleId = $this->input('id');
    
        return [
            'role_name' => [
                'required',
                'max:255',
                Rule::unique('crm_roles')
                    ->ignore($roleId)
                    ->where(function ($query) use ($companyId) {
                        if ($companyId === null) {
                            // If company_id is null (super admin role), check where company_id is NULL in the table
                            $query->whereNull('company_id');
                        } else {
                            // Otherwise, check for the specific company_id
                            $query->where('company_id', $companyId);
                        }
                    }),
            ],
            'role_desc' => 'nullable|string|max:1000',
        ];
    }
    

    public function messages()
    {
        return [
            'role_name' => 'Please add role name',
            'role_name.unique' => 'The role name has already been taken.',
            'role_desc' => 'Please enter only 1000 characters for role desc.'
        ];
    }
}
