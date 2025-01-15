<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Payment Gateway Store Sesstions table model handle all filters, observers, evenets, relatioships
 */
class PaymentGatewayStoreSession extends Model
{
    use HasFactory;

    const table = 'payment_gateway_store_session';

    protected $table = self::table;

    protected $fillable = [
        'company_id',
        'config_key',
        'config_value',
        'session_id',
        'mode'
    ];

    /**
     * company relations with payment_gateway_settings table
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
