<?php
namespace App\Traits;

use App\Models\Subscription;
use App\Models\SubscriptionUsage;
use Illuminate\Support\Facades\Auth;

trait SubscriptionUsageFilter
{


    public function checkCurrentUsage($module)
    {
        if ($this->isTenantUser()) {
            return true;
        }


        $totalAllow = $this->getTotalAllowed($module);

        // fetch current usage

        $currentUsage = $this->getCurrentUsage($this->getCurrentSubscriptionId());

        // check 
        if ($currentUsage >= $totalAllow) {

            return abort(redirect()->route(getPanelRoutes('planupgrade.index')));
        }
    
        return true;

    }


    public function updateUsage($module,$sign,$value)
    {
        if ($this->isTenantUser()) {
            return true;
        }


        // Get current subscription ID


        $subId = $this->getCurrentSubscriptionId();

        // Find existing usage
        $subUsageFind = SubscriptionUsage::where('subscription_id', $subId)
            ->where('company_id', $this->getCompanyId())
            ->first();




        if ($subUsageFind) {
            // Update existing record
            $subUsageFind->update([
                'value' => $subUsageFind->value .$sign. $value
            ]);

        } else {
            // Create new record
            SubscriptionUsage::create([
                'subscription_id' => $subId,
                'company_id' => $this->getCompanyId(),
                'module_name' => $module,
                'value' => 1
            ]);

        }
    }

    private function getTotalAllowed($module)
    {
        if ($this->isTenantUser()) {
            return true;
        }

        $currentSubscriptionsAllowed = Subscription::with([
            'plans' => function ($q) use ($module) {
                $q->with([
                    'planFeatures' => function ($q) use ($module) {
                        $q->where('module_name', $module);
                    }
                ]);
            }
        ])
            ->where('company_id', $this->getCompanyId())
            ->latest()
            ->first();


        return $currentSubscriptionsAllowed->plans->planFeatures[0]->value;


    }

    private function getCurrentSubscriptionId()
    {
        if (Auth::user()->is_tenant) {
            return;
        }

        $subscription = Subscription::where('company_id', $this->getCompanyId())->latest()->first();

        return $subscription->id;
    }

    private function getCurrentUsage(int $subId): int
    {
        // Find existing usage
        $subUsageFind = SubscriptionUsage::where('subscription_id', $subId)
            ->where('company_id', $this->getCompanyId())
            ->first();

        if ($subUsageFind) {
            return $subUsageFind->value;

        }
        return 0;
    }

    private function isTenantUser(): bool
    {
        return Auth::check() && Auth::user()->is_tenant;
    }
    private function getCompanyId(): bool
    {
        return Auth::user()->company_id;
    }
}