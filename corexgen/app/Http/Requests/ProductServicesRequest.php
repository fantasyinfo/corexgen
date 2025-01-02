<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductServicesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('PRODUCTS_SERVICES.CREATE')) {
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
            'type' => ['required', 'in:Product,Service'],
            'cgt_id' => ['nullable', 'exists:category_group_tag,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'max:2000'],
            'rate' => ['required', 'numeric'],
            'unit' => ['required', 'integer'],
            'tax_id' => ['nullable', 'exists:category_group_tag,id']
        ];
    }

}
