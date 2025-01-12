<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Company Onboarding table model handle all filters, observers, evenets, relatioships
 */
class CompanyOnboarding extends Model
{
    protected $table = 'company_onboarding';

    protected $fillable = [
        'company_id',
        'address',
        'currency_code',
        'currency_symbol',
        'timezone',
        'payment_completed',
        'plan_id',
        'payment_id',
        'status'
    ];

    /**
     * company relations with company onboarding table
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Method to update onboarding status
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }
}