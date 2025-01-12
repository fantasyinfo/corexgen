<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Plans table model handle all filters, observers, evenets, relatioships
 */
class Plans extends Model
{
    use HasFactory;


    const table = 'plans';

    protected $table = self::table;

    protected $fillable = ['name', 'desc', 'price', 'offer_price', 'billing_cycle', 'status'];


    /**
     * plan fetures relations with plans table
     */
    public function planFeatures()
    {
        return $this->hasMany(PlansFeatures::class, 'plan_id');
    }

    /**
     * subscription relations with plans table
     */
    public function subscription()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * company  relations with plans table
     */
    public function company()
    {
        return $this->hasOne(Company::class, 'plan_id');
    }

    /**
     * get plans distrubution
     */
    public function getPlanDistribution()
    {
        $sub = self::withCount('subscription') // Count related subscriptions for each plan
            ->get(['name']) // Retrieve only the plan name
            ->map(function ($plan) {
                return [
                    'name' => $plan->name,
                    'count' => $plan->subscription_count, // Subscription count provided by withCount
                ];
            });

        return [
            'labels' => $sub->pluck('name')->toArray(), // Plan names
            'data' => $sub->pluck('count')->toArray(), // Subscription counts
        ];

    }
}
