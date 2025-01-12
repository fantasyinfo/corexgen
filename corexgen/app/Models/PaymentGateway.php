<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Payment Gateway table model handle all filters, observers, evenets, relatioships
 */
class PaymentGateway extends Model
{
    use HasFactory;

    const table = 'payment_gateways';

    protected $table = self::table;

    protected $fillable = [
        'name',
        'official_website',
        'logo',
        'type',
        'config_key',
        'config_value',
        'mode',
        'status',

    ];
}
