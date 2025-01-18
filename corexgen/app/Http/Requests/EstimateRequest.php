<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstimateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('ESTIMATE.CREATE')) {
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
            '_prefix' => 'required|string',
            '_id' => 'required|string|max:10',
            'type' => 'required|in:client,lead',
            'client_id' => 'required_if:type,client|exists:clients,id',
            'lead_id' => 'required_if:type,lead|exists:leads,id',
            'title' => 'required|string|max:100',
            'value' => 'nullable|numeric|min:0',
            'details' => 'nullable|string',
            'creating_date' => 'required|date',
            'valid_date' => 'nullable|date|after_or_equal:today',
            'template_id' => 'nullable|exists:templates,id',
            'product_id.*' => 'nullable|string',
            'product_title.*' => 'required|string',
            'product_description.*' => 'nullable|string',
            'product_qty.*' => 'required|numeric|min:1',
            'product_rate.*' => 'required|numeric|min:0',
            'product_tax.*' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'adjustment' => 'nullable|numeric',

            // 
            '_ref_type' => 'nullable',
            '_ref_id' => 'nullable',
            '_ref_refrer' => 'nullable',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            '_prefix.required' => 'The estimate prefix is required.',
            '_prefix.string' => 'The estimate prefix must be a string.',
            '_prefix.max' => 'The estimate prefix must not exceed 50 characters.',

            '_id.required' => 'The estimate ID is required.',
            '_id.string' => 'The estimate ID must be a string.',
            '_id.max' => 'The estimate ID must not exceed 10 characters.',
            '_id.unique' => 'The estimate ID must be unique.',

            'type.required' => 'The type field is required.',
            'type.in' => 'The type must be either client or lead.',

            'client_id.required_if' => 'The client ID is required when the type is client.',
            'client_id.exists' => 'The selected client ID does not exist.',

            'lead_id.required_if' => 'The lead ID is required when the type is lead.',
            'lead_id.exists' => 'The selected lead ID does not exist.',

            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title must not exceed 100 characters.',

            'value.integer' => 'The value must be an integer.',
            'value.min' => 'The value must be at least 0.',

            'details.string' => 'The details must be a string.',

            'creating_date.required' => 'The creating date is required.',
            'creating_date.date' => 'The creating date must be a valid date.',

            'valid_date.required' => 'The valid till is required.',
            'valid_date.date' => 'The valid till must be a valid date.',
            'valid_date.after_or_equal' => 'The valid date must be today or later.',

            'template_id.integer' => 'The template ID must be an integer.',
            'template_id.exists' => 'The selected template ID does not exist.',

            'product_title.*.required' => 'Each product title is required.',
            'product_title.*.string' => 'Each product title must be a string.',

            'product_description.*.string' => 'Each product description must be a string.',

            'product_qty.*.required' => 'Each product quantity is required.',
            'product_qty.*.numeric' => 'Each product quantity must be a number.',
            'product_qty.*.min' => 'Each product quantity must be at least 1.',

            'product_rate.*.required' => 'Each product rate is required.',
            'product_rate.*.numeric' => 'Each product rate must be a number.',
            'product_rate.*.min' => 'Each product rate must be at least 0.',

            'product_tax.*.string' => 'Each product tax must be a string.',
        ];
    }
}
