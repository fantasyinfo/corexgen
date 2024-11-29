<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlansRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('PLANS.CREATE')) {
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
            'name' => ['string', 'required'],
            'desc' => ['string', 'required'],
            'price' => ['integer', 'required'],
            'offer_price' => ['integer', 'required'],
            'billing_cycle' => ['required'],
            'users_limit' => ['integer'],
            'roles_limit' => ['integer'],
        ];
    }
}
