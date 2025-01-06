<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return hasPermission('PROJECTS.CREATE');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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

    /**
     * Get the custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The project title is required.',
            'title.string' => 'The project title must be a valid string.',
            'title.max' => 'The project title must not exceed 200 characters.',

            'description.string' => 'The description must be a valid string.',

            'billing_type.required' => 'The billing type is required.',
            'billing_type.in' => 'The billing type must be either "Hourly" or "One-Time".',

            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'The start date must be a valid date.',

            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after_or_equal' => 'The due date must be on or after the start date.',

            'deadline.date' => 'The deadline must be a valid date.',
            'deadline.after_or_equal' => 'The deadline must be on or after the start date.',

            'estimated_hours.integer' => 'The estimated hours must be an integer.',
            'estimated_hours.min' => 'The estimated hours must be at least 0.',

            'time_spent.integer' => 'The time spent must be an integer.',
            'time_spent.min' => 'The time spent must be at least 0.',

            'client_id.required' => 'The client ID is required.',
            'client_id.exists' => 'The selected client does not exist.',

            'one_time_cost.required_if' => 'The one-time cost is required when the billing type is "One-Time".',
            'one_time_cost.numeric' => 'The one-time cost must be a valid number.',

            'per_hour_cost.required_if' => 'The per-hour cost is required when the billing type is "Hourly".',
            'per_hour_cost.numeric' => 'The per-hour cost must be a valid number.',

            'progress.integer' => 'The progress must be an integer.',
            'progress.min' => 'The progress must be at least 0.',
            'progress.max' => 'The progress may not be greater than 100.',
        ];
    }
}
