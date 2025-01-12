<?php

namespace App\Repositories;

use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Traits\TenantFilter;

class PlansPaymentTransactionsRepository
{
    // Your repository methods
    use TenantFilter;

    /**
     * get transaction lists query
     */
    public function getTransactionQuery($request)
    {
        // dd($request->all());
        $query = PaymentTransaction::with(['plans', 'subscription', 'company']);

        return $query;

        // Dynamic filters
        // return $this->applyFilters($query, $request);
    }

    /**
     * get subscriptions lists query
     */
    public function getSubscriptionsQuery($request)
    {
        $query = Subscription::with(['plans', 'company', 'payment_transaction']);

        return $query;

        // Dynamic filters
        // return $this->applyFilters($query, $request);
    }


}
