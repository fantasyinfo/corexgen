<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class TaxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('TAX.CREATE')) {
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
        $tax_id = $this->input('id'); // countries.id

        return [
            'name' => ['required', 'string'],
            'tax_type' => ['required', 'string'],
            'tax_rate' => ['required'],
            'country_id' => [
                'required',
                Rule::unique('tax_rates', 'country_id')->ignore($tax_id), // Ignore current tax ID
            ],
        ];
    }
}
