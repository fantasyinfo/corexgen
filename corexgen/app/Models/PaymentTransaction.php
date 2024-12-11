<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;


    const table = 'payment_transactions';

    protected $table = self::table;

    protected $fillable = [
        'plan_id',
        'company_id',
        'amount',
        'currency',
        'payment_gateway',
        'payment_type',
        'transaction_refrence',
        'status',
        'transaction_date'

    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_id');
    }

    public function plans(){
        return $this->belongsTo(Plans::class,'plan_id');
    }

    public function subscription(){
        return $this->hasOne(Subscription::class,'payment_id');
     }
        // boot method
        protected static function boot()
        {
            parent::boot();
    
            static::creating(function ($paymentTransaction) {
                // Set default values
                $paymentTransaction->status = $paymentTransaction->status ?? CRM_STATUS_TYPES['PAYMENTSTRANSACTIONS']['STATUS']['SUCCESS'];
            });
        }
}
