<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('INVOICES.CREATE')) {
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
            '_id' => 'required|string|max:10',
            'client_id' => 'required|exists:clients,id',
            'notes' => 'nullable|string',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:today',
            'task_id' => 'nullable|exists:tasks,id',
            'total_amount' => 'required|numeric|min:1',
            'project_id' => 'nullable|exists:projects,id',
            'timesheet_id' => 'nullable|exists:timesheets,id',

            // Ensure at least one product is provided
            'product_title.0' => 'required|string',
            'product_description.0' => 'nullable|string',
            'product_qty.0' => 'required|numeric|min:1',
            'product_rate.0' => 'required|numeric|min:0',
            'product_tax.0' => 'nullable|string',

            // Validation for all products
            'product_id.*' => 'nullable|string',
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
