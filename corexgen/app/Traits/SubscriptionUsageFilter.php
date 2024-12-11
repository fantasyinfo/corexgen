<?php
namespace App\Traits;

use App\Models\Subscription;
use App\Models\SubscriptionUsage;
use Illuminate\Support\Facades\Auth;

trait SubscriptionUsageFilter
{



    public int $companyid;

    public function checkCurrentUsage($module)
    {
        if (Auth::user()->is_tenant) {
            return true;
        }
        $this->companyid = Auth::user()->company_id;

        $totalAllow = $this->getTotalAllowed($this->companyid, $module);

        // fetch current usage

    }


    public function updateUsage($module)
    {
        if (Auth::user()->is_tenant) {
            return;
        }
 
        $this->companyid = Auth::user()->company_id;
  
        // Get current subscription ID
      

        $subId = $this->getCurrentSubscriptionId();
  
        // Find existing usage
        $subUsageFind = SubscriptionUsage::where('subscription_id', $subId)
            ->where('company_id', $this->companyid)
            ->first();
    
     
     

        if ($subUsageFind) {
            // Update existing record
            $subUsageFind->update([
                'value' => $subUsageFind->value + 1
            ]);
         
        } else {
            // Create new record
            SubscriptionUsage::create([
                'subscription_id' => $subId,
                'company_id' => $this->companyid,
                'module_name' => $module,
                'value' => 1
            ]);

        }
    }

    private function getTotalAllowed($companyid, $module)
    {
        if (Auth::user()->is_tenant) {
            return;
        }

        $this->companyid = $companyid;
        $currentSubscriptionsAllowed = Subscription::with([
            'plans' => function ($q) use ($module) {
                $q->with([
                    'planFeatures' => function ($q) use ($module) {
                        $q->where('module_name', $module);
                    }
                ]);
            }
        ])
            ->where('company_id', $this->companyid)
            ->latest()
            ->first();


        return [
            'id' => $currentSubscriptionsAllowed->id,
            'value' => $currentSubscriptionsAllowed->plans->planFeatures[0]->value,
        ];

    }

    private function getCurrentSubscriptionId()
    {
        if (Auth::user()->is_tenant) {
            return;
        }

        $this->companyid = Auth::user()->company_id;

        $subscription = Subscription::where('company_id', $this->companyid)->latest()->first();
     
        return $subscription->id;
    }
}