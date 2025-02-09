<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CompaniesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // if (hasPermission('COMPANIES.CREATE')) {
        //     return true;
        // }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $companyId = $this->input('id') ?? null;
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');


        return [
            'id' => [$isUpdate ? 'required' : 'nullable'],
            'name' => ['required', 'string','unique:companies,name','max:150'],
            'cname' => ['required', 'string','max:150'],
            'email' => [
                'required',
                'email',
                $this->uniqueEmailRule($companyId),
            ],
            'phone' => ['required', 'digits_between:10,15'],
            'password' => [$isUpdate ? 'nullable' : 'required', 'min:8'],
            'plan_id' => ['required', 'exists:plans,id'],
            'address_street_address' => 'nullable|string|max:255',
            'address_country_id' => 'nullable|exists:countries,id',
            'address_city_name' => 'nullable|max:100',
            'address_pincode' => 'nullable|string|max:10',

            // adding front parametwres
            'gateway' => 'nullable',
            'from_admin' => 'nullable',
        ];
    }


    protected function uniqueEmailRule($companyId)
    {
        return function ($attribute, $value, $fail) use ($companyId) {
            $userExists = User::where('email', $value)
                ->where('company_id', '!=', $companyId)
                ->exists();

            $companyExists = Company::where('email', $value)
                ->where('id', '!=', $companyId)
                ->exists();

            if ($userExists || $companyExists) {
                $fail('The email has already been taken.');
            }
        };
    }
}
