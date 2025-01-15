<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payment Transation Company table model handle all filters, observers, evenets, relatioships
 */
class PaymentTransactionsCompany extends Model
{
    use HasFactory;

    const table = 'payment_transactions_company';

    protected $table = self::table;

    protected $fillable = [
        'company_id',
        'amount',
        'currency',
        'payment_gateway',
        'payment_type',
        'transaction_reference',
        'status',
        'transaction_date',
        
    ];

    /**
     * company relations with payment transation table
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
