<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use App\Traits\TenantFilter;

class CompanyRepository
{
    use TenantFilter;
    // Your repository methods

    public function getCompanyQuery($request)
    {
        $query = Company::query()
            ->select('companies.*')
            ->leftJoin('plans', 'companies.plan_id', '=', 'plans.id')
            ->leftJoin('subscriptions', function($join) {
                $join->on('companies.id', '=', 'subscriptions.company_id')
                     ->whereRaw('subscriptions.id = (
                         SELECT id 
                         FROM subscriptions as s2 
                         WHERE s2.company_id = companies.id 
                         ORDER BY s2.created_at DESC 
                         LIMIT 1
                     )');
            })
            ->with('plans', 'latestSubscription');
    
        // Dynamic filters
        return $this->applyFilters($query, $request);
    }

    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('name'),
                fn($q) => $q->where('companies.name', 'LIKE', "%{$request->name}%")
            )
            ->when(
                $request->filled('email'),
                fn($q) => $q->where('companies.email', 'LIKE', "%{$request->email}%")
            )
            ->when(
                $request->filled('status'),
                fn($q) => $q->where('companies.status', $request->status)
            )
            ->when(
                $request->filled('start_date'),
                fn($q) => $q->whereDate('subscriptions.start_date', '>=', $request->start_date)
            )
            ->when(
                $request->filled('end_date'),
                fn($q) => $q->whereDate('subscriptions.end_date', '<=', $request->end_date)
            )
            ->when(
                $request->filled('next_billing_date'),
                fn($q) => $q->whereDate('subscriptions.next_billing_date', '=', $request->next_billing_date)
            )
            ->when(
                $request->filled('plans'),
                fn($q) => $q->where('plans.name', 'LIKE', "%{$request->plans}%")
            );
    }
    
}
