<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



/**
 * Subscriptions Usage table model handle all filters, observers, evenets, relatioships
 */
class SubscriptionUsage extends Model
{
    use HasFactory;

    const table = 'subscriptions_usage';

    protected $table = self::table;

    protected $fillable = [
        'subscription_id',
        'company_id',
        'module_name',
        'value',
    ];

}
