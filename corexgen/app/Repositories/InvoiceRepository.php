<?php

namespace App\Repositories;

use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\Invoice;
use App\Traits\TenantFilter;
use Illuminate\Support\Carbon;

class InvoiceRepository
{

    use TenantFilter;
    // Your repository methods
    /**
     * get invoice lists query
     */
    public function getInvoiceQuery($request)
    {
        $query = Invoice::query()->with(['task', 'client', 'project'])->latest();

        $query = $this->applyTenantFilter($query);

        // Dynamic filters
        //return $query;
        return $this->applyFilters($query, $request);
    }

    /**
     * get invoice lists query filter
     */
    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('client_id') && $request->client_id != '0',
                fn($q) => $q->where('client_id', $request->client_id)
            )
            ->when(
                $request->filled('task_id') && $request->task_id != '0',
                fn($q) => $q->where('task_id', $request->task_id)
            )
            ->when(
                $request->filled('status') && $request->task_id != '0',
                fn($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->filled('issue_date'),
                fn($q) => $q->whereDate('issue_date', Carbon::parse($request->issue_date)->toDateString())
            )->when(
                $request->filled('due_date'),
                fn($q) => $q->whereDate('due_date', Carbon::parse($request->due_date)->toDateString())
            );
    }
}
