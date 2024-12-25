<?php

namespace App\Repositories;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;

class LeadsRepository
{
    // Your repository methods

    public function getLeadsQuery($request)
    {
        $query = CRMLeads::query()
            ->select([
                'leads.id',  // Specify table name to avoid ambiguity
                'leads.type',
                'leads.company_name',
                'leads.title',
                'leads.first_name',
                'leads.last_name',
                'leads.email',
                'leads.phone',
                'leads.status',
                'leads.created_at',
                'leads.group_id',
                'leads.source_id',
                'leads.status_id',
                'leads.address_id',
                'leads.assign_by'
            ])
            ->with([
                'group:id,name,color',
                'source:id,name,color',
                'stage:id,name,color',
                'address' => fn($q) => $q
                    ->select(['id', 'street_address', 'postal_code', 'city_id', 'country_id'])
                    ->with([
                        'city:id,name',
                        'country:id,name'
                    ]),
                'assignedBy:id,name',
                'assignees' => fn($q) => $q
                    ->select(['users.id', 'users.name'])
                    ->withOnly([])
            ]);
    
        return $this->applyFilters($query, $request);
    }
    
    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('search'),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $searchTerm = strtolower($request->search['value']);
                    $subQuery->where('leads.type', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.company_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.title', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.first_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas('group', fn($groupQuery) => 
                            $groupQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        )
                        ->orWhereHas('source', fn($sourceQuery) => 
                            $sourceQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        )
                        ->orWhereHas('stage', fn($stageQuery) => 
                            $stageQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        )
                        ->orWhere('leads.created_at', 'LIKE', "%{$searchTerm}%");
                })
            )
            ->when(
                $request->filled('name'),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $subQuery->where('leads.first_name', 'LIKE', "%{$request->name}%")
                        ->orWhere('leads.last_name', 'LIKE', "%{$request->name}%");
                })
            )
            ->when(
                $request->filled('email'),
                fn($q) => $q->where('leads.email', 'LIKE', "%{$request->email}%")
            )
            ->when(
                $request->filled('phone'),
                fn($q) => $q->where('leads.phone', 'LIKE', "%{$request->phone}%")
            )
            ->when(
                $request->filled('status') && $request->status != 0,
                fn($q) => $q->where('leads.status', $request->status)
            )
            ->when(
                $request->filled('start_date'),
                fn($q) => $q->whereDate('leads.created_at', '>=', $request->start_date)
            )
            ->when(
                $request->filled('end_date'),
                fn($q) => $q->whereDate('leads.created_at', '<=', $request->end_date)
            )
            ->when(
                $request->filled('assign_to'),
                fn($q) => $q->whereHas('assignees', fn($assigneeQuery) => 
                    $assigneeQuery->whereIn('users.id', $request->assign_to)
                )
            )
            ->when(
                $request->filled('assign_by'),
                fn($q) => $q->whereHas('assignedBy', fn($assignByQuery) => 
                    $assignByQuery->where('users.id', $request->assign_by)
                )
            );
    }
    
}
