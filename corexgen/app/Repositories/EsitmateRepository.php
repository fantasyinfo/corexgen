<?php

namespace App\Repositories;

use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMEstimate;
use App\Traits\TenantFilter;
use Illuminate\Support\Carbon;

class EsitmateRepository
{

    use TenantFilter;
    // Your repository methods

    /**
     * get estimate lists query
     */
    public function getEstimateQuery($request)
    {
        $query = CRMEstimate::query()->latest();

        $query = $this->applyTenantFilter($query);

        // Dynamic filters
        return $this->applyFilters($query, $request);
    }

    /**
     * get estimate lists query filtes
     */

    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('title'),
                fn($q) => $q->where('title', 'LIKE', "%{$request->title}%")
            )
            ->when(
                $request->filled('client_id') && $request->client_id != '0',
                fn($q) => $q->where('typable_type', CRMClients::class)->where('typable_id', $request->client_id)
            )
            ->when(
                $request->filled('lead_id') && $request->client_id != '0',
                fn($q) => $q->where('typable_type', CRMLeads::class)->where('typable_id', $request->lead_id)
            )
            ->when(
                $request->filled('status'),
                fn($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->filled('creating_date'),
                fn($q) => $q->whereDate('created_at', Carbon::parse($request->creating_date)->toDateString())
            )->when(
                $request->filled('valid_date'),
                fn($q) => $q->whereDate('created_at', Carbon::parse($request->valid_date)->toDateString())
            );
    }
}
