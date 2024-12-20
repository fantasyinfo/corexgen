<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientsEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('CLIENTS.UPDATE')) {
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
            // Basic Information
            'id' => ['required','exists:clients,id'],
            'type' => ['required', Rule::in(['Individual', 'Company'])],
            'title' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birthdate' => ['nullable', 'date', 'before:today'],

            // Contact Information (Multiple Emails & Phones)
            'email' => ['required', 'array', 'min:1'],
            'email.*' => ['required', 'email', 'max:255', 'distinct'],

            'phone' => ['nullable', 'array'],
            'phone.*' => ['required', 'string', 'max:20', 'distinct'],

            // Social Media
            'social_media' => ['nullable', 'array'],
            'social_media.*' => ['nullable'],

            // Category and Status
            'category' => ['nullable', Rule::in(CLIENTS_CATEGORY_TYPES['TABLE_STATUS'])],

            // Additional Details
            'details' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],

            // Addresses
            'addresses' => ['nullable', 'array'],
            'addresses.*.type' => ['required', Rule::in(['home', 'billing', 'shipping', 'custom'])],
            'addresses.*.street_address' => ['nullable', 'string', 'max:500'],
            'addresses.*.country_id' => ['nullable', 'exists:countries,id'],
            'addresses.*.city' => ['nullable', 'string', 'max:100'],
            'addresses.*.pincode' => ['nullable', 'string', 'max:20'],
        ];
    }


    public function messages()
    {
        return [
            // Basic Information Messages
            'id' => 'Please provide client id',
            'type.required' => 'Please select a client type',
            'type.in' => 'Invalid client type selected',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'birthdate.before' => 'Birth date must be a date before today',

            // Contact Information Messages
            'email.required' => 'At least one email address is required',
            'email.*.email' => 'Please enter a valid email address',
            'email.*.distinct' => 'Duplicate email addresses are not allowed',

            'phone.required' => 'At least one phone number is required',
            'phone.*.distinct' => 'Duplicate phone numbers are not allowed',

            // Social Media Messages


            // Address Messages
            'addresses.required' => 'At least one address is required',
            'addresses.*.type.required' => 'Please select an address type',
            'addresses.*.type.in' => 'Invalid address type selected',
            'addresses.*.street_address.required' => 'Street address is required',
            'addresses.*.country_id.required' => 'Please select a country',
            'addresses.*.country_id.exists' => 'Selected country is invalid',
            'addresses.*.city.required' => 'City is required',
            'addresses.*.pincode.required' => 'Pincode/ZIP code is required'
        ];
    }

    protected function prepareForValidation()
    {
        // Clean up tags
        if ($this->has('tags')) {
            $tags = array_map('trim', $this->tags);
            $tags = array_filter($tags);
            $this->merge(['tags' => array_values(array_unique($tags))]);
        }
    }



    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', $this->input('active_tab', 'general'))
        );
    }
}
