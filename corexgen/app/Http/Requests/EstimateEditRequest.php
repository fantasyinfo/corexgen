<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstimateEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('ESTIMATE.UPDATE')) {
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
            'id' => 'required|exists:proposals,id',
            'type' => 'required|in:client,lead',
            'client_id' => 'required_if:type,client|exists:clients,id',
            'lead_id' => 'required_if:type,lead|exists:leads,id',
            'title' => 'required|string|max:100',
            'value' => 'nullable|numeric|min:0',
            'details' => 'nullable|string',
            'creating_date' => 'required|date',
            'valid_date' => 'nullable|date|after_or_equal:today',
            'template_id' => 'nullable|exists:templates,id',


            'product_title.*' => 'required|string',
            'product_description.*' => 'nullable|string',
            'product_qty.*' => 'required|numeric|min:1',
            'product_rate.*' => 'required|numeric|min:0',
            'product_tax.*' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'adjustment' => 'nullable|numeric',
        ];
    }
}
