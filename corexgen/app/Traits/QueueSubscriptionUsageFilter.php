<?php
namespace App\Traits;

use App\Models\Subscription;
use App\Models\SubscriptionUsage;

trait QueueSubscriptionUsageFilter
{
    protected $userId;
    protected $companyId;
    protected $isTenant;

    /**
     * Initialize the trait with user data
     * 
     * @param array $userData
     * @return void
     */
    public function initializeUsageFilter(array $userData)
    {
        $this->userId = $userData['user_id'] ?? null;
        $this->companyId = $userData['company_id'] ?? null;
        $this->isTenant = $userData['is_tenant'] ?? false;
    }

    public function checkCurrentUsage($module)
    {
        if ($this->isTenant) {
            return true;
        }

        $totalAllow = $this->getTotalAllowed($module);

        // fetch current usage
        $currentUsage = $this->getCurrentUsage($this->getCurrentSubscriptionId(), $module);

        \Log::info('Usage', [
            'currentUsage' => $currentUsage,
            'totalAllow' => $totalAllow,
            'companyId' => $this->companyId,
            'currentSubID' => $this->getCurrentSubscriptionId()
        ]);

        // for unlimited
        if ($totalAllow == '-1') {
            return true;
        }

        if ($currentUsage >= $totalAllow) {
            // Instead of redirect, throw an exception that can be caught by the queue job
            throw new \Exception('Subscription limit reached. Please upgrade the plan.');
        }

        return true;
    }

    public function updateUsage($module, $sign, $value)
    {
        if ($this->isTenant) {
            return true;
        }

        // Get current subscription ID
        $subId = $this->getCurrentSubscriptionId();

        // Find existing usage
        $subUsageFind = SubscriptionUsage::where('subscription_id', $subId)
            ->where('company_id', $this->companyId)
            ->where('module_name', $module)
            ->first();

        if ($subUsageFind) {
            if ($sign === '+') {
                $newValue = $subUsageFind->value + $value;
            } else {
                if ($subUsageFind->value <= 0) {
                    return false;
                }
                $newValue = max(0, $subUsageFind->value - $value);
            }

            $subUsageFind->update([
                'value' => $newValue
            ]);
        } else {
            if ($sign === '+') {
                SubscriptionUsage::create([
                    'subscription_id' => $subId,
                    'company_id' => $this->companyId,
                    'module_name' => $module,
                    'value' => $value
                ]);
            } else {
                return false;
            }
        }

        return true;
    }

    private function getTotalAllowed($module)
    {
        if ($this->isTenant) {
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
            ->where('company_id', $this->companyId)
            ->latest()
            ->first();

        return $currentSubscriptionsAllowed->plans->planFeatures[0]->value;
    }

    private function getCurrentSubscriptionId()
    {
        if ($this->isTenant) {
            return null;
        }

        $subscription = Subscription::where('company_id', $this->companyId)
            ->latest()
            ->first();

        return $subscription->id;
    }

    private function getCurrentUsage(int $subId, $module): int
    {
        $subUsageFind = SubscriptionUsage::where('subscription_id', $subId)
            ->where('company_id', $this->companyId)
            ->where('module_name', $module)
            ->first();

        return $subUsageFind ? $subUsageFind->value : 0;
    }
}