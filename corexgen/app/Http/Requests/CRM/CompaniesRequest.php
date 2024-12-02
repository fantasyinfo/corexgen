<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class CompaniesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('COMPANIES.CREATE')) {
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
        return [
            'name' => ['required', 'string',], 
            'email' => ['required','email','unique:companies','unique:users'], 
            'phone' => ['required','min:10'], 
            'password' => ['required','min:8'], 
            'plan_id' => ['required','exists:plans,id'], 
            'address_street_address' => 'nullable|string|max:255',
            'address_country_id' => 'nullable',
            'address_city_id' => 'nullable',
            'address_pincode' => 'nullable|string|max:10',
        ];
    }
}
