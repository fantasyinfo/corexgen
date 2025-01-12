<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Subscriptions table model handle all filters, observers, evenets, relatioships
 */
class Subscription extends Model
{
    use HasFactory;
    use SoftDeletes;


    const table = 'subscriptions';

    protected $table = self::table;

    protected $fillable = [
        'plan_id',
        'company_id',
        'payment_id',
        'start_date',
        'end_date',
        'next_billing_date',
        'billing_cycle',
        'pervious_plan_id',
        'upgrade_date',
        'status',
    ];


    /**
     * company relations with subscriptions table
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * plans relations with subscriptions table
     */
    public function plans()
    {
        return $this->belongsTo(Plans::class, 'plan_id');
    }

    /**
     * payment transactions relations with subscriptions table
     */
    public function payment_transaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_id');
    }

    /**
     * usage relations with subscriptions table
     */
    public function usages()
    {
        return $this->hasMany(SubscriptionUsage::class, 'subscription_id');
    }

    /**
     * total Subscriptions relations with subscriptions table
     */
    public function totalSubscriptions($status = CRM_STATUS_TYPES['SUBSCRIPTION']['STATUS']['ACTIVE'])
    {
        return self::where('status', $status)->count();

    }

    // boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paymentTransaction) {
            // Set default values
            $paymentTransaction->status = $paymentTransaction->status ?? CRM_STATUS_TYPES['SUBSCRIPTION']['STATUS']['ACTIVE'];
        });
    }
}

