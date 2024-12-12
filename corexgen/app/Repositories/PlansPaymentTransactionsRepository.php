<?php

namespace App\Repositories;

use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Traits\TenantFilter;

class PlansPaymentTransactionsRepository
{
    // Your repository methods
    use TenantFilter;

    public function getTrnasactionQuery($request)
    {
        $query = PaymentTransaction::with(['plans', 'subscription', 'company']);

        return $query = $query->get();

        // Dynamic filters
        // return $this->applyFilters($query, $request);
    }
    public function getSubscriptionsQuery($request)
    {
        $query = Subscription::with(['plans', 'company','payment_transaction']);

        return $query = $query->get();

        // Dynamic filters
        // return $this->applyFilters($query, $request);
    }


}
