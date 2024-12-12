<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
