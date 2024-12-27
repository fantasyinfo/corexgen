<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('LEADS.CREATE')) {
            return true;
        }
        return false; // Allow all authenticated users to proceed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:Individual,Company',
            'company_name' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'value' => 'nullable|numeric|min:0|max:999999999999.99',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:leads,email',
            'phone' => 'nullable|min:7|max:15|unique:leads,phone',
            'details' => 'nullable|string',
            'last_contacted_date' => 'nullable|date',
            'last_activity_date' => 'nullable|date',
            'priority' => 'required|in:Low,Medium,High',
            'preferred_contact_method' => 'nullable|in:Email,Phone,In-Person',
            'score' => 'nullable|integer|min:0|max:100',
            'follow_up_date' => 'nullable|date',
            'is_converted' => 'nullable',
            'group_id' => 'nullable|exists:category_group_tag,id',
            'source_id' => 'nullable|exists:category_group_tag,id',
            'status_id' => 'nullable|exists:category_group_tag,id',
            'address_street_address' => 'nullable|string|max:255',
            'address_country_id' => 'nullable|exists:countries,id',
            'address_city_name' => 'nullable|string|max:255',
            'address_pincode' => 'nullable|string|max:20',
            'assign_to' => 'array|nullable|exists:users,id'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'The lead type is required.',
            'type.in' => 'The lead type must be either "Individual" or "Company".',
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'value.numeric' => 'The value must be a number.',
            'value.min' => 'The value must be at least 0.',
            'value.max' => 'The value may not exceed 999,999,999,999.99.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'This email is already associated with another lead.',
            'phone.digits_between' => 'The phone number must be between 7 and 15 digits.',
            'phone.unique' => 'This phone number is already associated with another lead.',
            'priority.required' => 'The priority field is required.',
            'priority.in' => 'The priority must be Low, Medium, or High.',
            'group_id.exists' => 'The selected group is invalid.',
            'source_id.exists' => 'The selected source is invalid.',
            'status_id.exists' => 'The selected status is invalid.',
            'address_country_id.exists' => 'The selected country is invalid.',
            'follow_up_date.date' => 'The follow-up date must be a valid date.',
            'last_contacted_date.date' => 'The last contacted date must be a valid date.',
            'last_activity_date.date' => 'The last activity date must be a valid date.',
            
        ];
    }
}
