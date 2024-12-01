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


        $baseRules = [
            'name' => ['string', 'required'],
            'desc' => ['string', 'required'],
            'price' => ['numeric', 'required'],
            'offer_price' => ['numeric', 'required'],
            'billing_cycle' => ['required'],
        ];
    
        // Add validation rules for PLANS_FEATURES
        $featureRules = [];
        foreach (PLANS_FEATURES as $featureKey) {
            $featureKey = strtolower(str_replace(' ', '_', $featureKey));
            $featureRules["features_{$featureKey}"] = ['required', 'numeric']; 
        }
    
        return array_merge($baseRules, $featureRules);
    }

    public function messages(): array
{
    return [
        'features.*.required' => 'The :attribute is required for all plan features.',
        'features.*.integer' => 'The :attribute must be a valid integer.',
   
    ];
}
}
