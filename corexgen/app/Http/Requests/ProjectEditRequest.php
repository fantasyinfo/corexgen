<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return hasPermission('PROJECTS.UPDATE');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required','exists:projects,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'billing_type' => ['required', 'in:Hourly,One-Time'],
            'start_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['nullable', 'integer', 'min:0'],
            'time_spent' => ['integer', 'min:0'],
            'client_id' => ['required', 'exists:clients,id'],
            'one_time_cost' => ['nullable', 'numeric', 'required_if:billing_type,One-Time'],
            'per_hour_cost' => ['nullable', 'numeric', 'required_if:billing_type,Hourly'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'assign_to' => 'array|nullable|exists:users,id'
        ];
    }
}
