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

        // for unlimited
        if ($totalAllow == '-1') {
            return true;
        }

        // fetch current usage

        $currentUsage = $this->getCurrentUsage($this->getCurrentSubscriptionId());

        \Log::info('Usage', [
            'currentUsage' => $currentUsage,
            'totalAllow' => $totalAllow,
            'companyId' => $this->getCompanyId(),
            'currentSubID' => $this->getCurrentSubscriptionId()
        ]);
        // check 

        if ($currentUsage >= $totalAllow) {

            return abort(redirect()->route(getPanelRoutes('planupgrade.index'))->with('error', 'Please Upgrade the plan, current plan resources exceeds'));
        }

        return true;

    }


    public function updateUsage($module, $sign, $value)
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
            // Perform mathematical calculation based on sign
            if ($sign === '+') {
                $newValue = $subUsageFind->value + $value;
            } else {
                // Ensure value does not go negative
                if ($subUsageFind->value <= 0) {
                    return false; // Prevent updating to a negative value
                }

                $newValue = max(0, $subUsageFind->value - $value);
            }

            // Update existing record
            $subUsageFind->update([
                'value' => $newValue
            ]);

        } else {
            // If creating a new record, only allow if the sign is '+'
            if ($sign === '+') {
                SubscriptionUsage::create([
                    'subscription_id' => $subId,
                    'company_id' => $this->getCompanyId(),
                    'module_name' => $module,
                    'value' => $value
                ]);
            } else {
                return false; // Do not create negative records
            }
        }

        return true; // Indicate successful operation
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