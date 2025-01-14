<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySettings extends Model
{
    use HasFactory;

    const table = 'payment_gateway_settings';

    protected $table = self::table;

    protected $fillable = [
        'company_id',
        'config_key',
        'config_value',
        'payment_gateway_id',
        'status',
        'mode'
    ];

    /**
     * company relations with payment_gateway_settings table
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * payment Gateway relations with payment_gateway_settings table
     */
    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }
}
