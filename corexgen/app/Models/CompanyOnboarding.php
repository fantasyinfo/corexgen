<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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