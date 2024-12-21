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

        $query = Company::query()->with(['plans', 'latestSubscription']);

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
                $request->filled('status')  && $request->status != '0',
                fn($q) => $q->where('companies.status', $request->status)
            )
            ->when(
                $request->filled('start_date'),
                fn($q) => $q->whereDate('latestSubscription.start_date', '>=', $request->start_date)
            )
            ->when(
                $request->filled('end_date'),
                fn($q) => $q->whereDate('latestSubscription.end_date', '<=', $request->end_date)
            )
            ->when(
                $request->filled('next_billing_date'),
                fn($q) => $q->whereDate('latestSubscription.next_billing_date', '=', $request->next_billing_date)
            )
            ->when(
                $request->filled('plan_id'),
                fn($q) => $q->where('plan_id', '=', $request->plan_id)
            )
            ->when(
                $request->filled('plans') && $request->plans != '0',
                fn($q) => $q->whereHas('plans', function ($subQuery) use ($request) {
                    $subQuery->where('name', '=', $request->plans);
                })
            )
            ->when(
                $request->filled('billing_cycle'),
                fn($q) => $q->whereHas('plans', function ($subQuery) use ($request) {
                    $subQuery->where('billing_cycle', '=', $request->billing_cycle);
                })
            );
    }

}
