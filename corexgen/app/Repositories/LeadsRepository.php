<?php

namespace App\Repositories;
use App\Models\CategoryGroupTag;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LeadsRepository
{
    // Your repository methods

    /**
     * get leads lists query
     */
    public function getLeadsQuery($request)
    {

        $wantCurrentUserItems = filter_var($request->input('current_user'), FILTER_VALIDATE_BOOLEAN);

        $query = CRMLeads::query()->latest()
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


        // Apply filtering based on the current_user flag
        if ($wantCurrentUserItems === true) {
            $query->whereHas('assignees', function ($query) {
                $query->where('users.id', Auth::id());
            });
        }

        return $this->applyFilters($query, $request);
    }

    /**
     * get leads lists query filter
     */

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
                        ->orWhereHas(
                            'group',
                            fn($groupQuery) =>
                            $groupQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        )
                        ->orWhereHas(
                            'source',
                            fn($sourceQuery) =>
                            $sourceQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        )
                        ->orWhereHas(
                            'stage',
                            fn($stageQuery) =>
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
                $request->filled('status_id') && $request->status_id != 0,
                fn($q) => $q->where('leads.status_id', $request->status_id)
            )
            ->when(
                $request->filled('group_id') && $request->group_id != 0,
                fn($q) => $q->where('leads.group_id', $request->group_id)
            )
            ->when(
                $request->filled('source_id') && $request->source_id != 0,
                fn($q) => $q->where('leads.source_id', $request->source_id)
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
                $request->filled('assign_to') && count($request->assign_to) != 0,
                fn($q) => $q->whereHas(
                    'assignees',
                    fn($assigneeQuery) =>
                    $assigneeQuery->whereIn('users.id', $request->assign_to)
                )
            )
            ->when(
                $request->filled('assign_by') && $request->assign_by != 0,
                fn($q) => $q->whereHas(
                    'assignedBy',
                    fn($assignByQuery) =>
                    $assignByQuery->where('users.id', $request->assign_by)
                )
            );
    }

    /**
     * get kanban lists query
     */

    public function getKanbanLoad($request)
    {
        $wantCurrentUserItems = false;
        if (isset($request['query']['current_user']) && $request['query']['current_user'] == true) {
            $wantCurrentUserItems = filter_var($request['query']['current_user'], FILTER_VALIDATE_BOOLEAN);
        }

        $query = CRMLeads::query()->latest()
            ->select([
                'leads.id',
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
                'leads.assign_by',
                'category_group_tag.name as stage_name'
            ])
            ->join('category_group_tag', 'leads.status_id', '=', 'category_group_tag.id')
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
            ])->orderBy('leads.updated_at', 'desc');

        if ($wantCurrentUserItems === true) {
            $query->whereHas('assignees', function ($query) {
                $query->where('users.id', Auth::id());
            });
        }

        return $this->applyKanbanFilters($query, $request['filters']);
    }

    /**
     * get kanban lists query filters
     */
    protected function applyKanbanFilters($query, $request)
    {

        return $query
            ->when(
                $request['search'] && $request['search'] != '',
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $searchTerm = strtolower($request['search']);
                    $subQuery->where('leads.type', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.company_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.title', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.first_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('leads.phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas(
                            'group',
                            fn($groupQuery) =>
                            $groupQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        )
                        ->orWhereHas(
                            'source',
                            fn($sourceQuery) =>
                            $sourceQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        )
                        ->orWhereHas(
                            'stage',
                            fn($stageQuery) =>
                            $stageQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        )
                        ->orWhere('leads.created_at', 'LIKE', "%{$searchTerm}%");
                })
            )
            ->when(
                isset($request['name']) && !empty($request['name']),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $subQuery->where('leads.first_name', 'LIKE', "%" . $request['name'] . "%")
                        ->orWhere('leads.last_name', 'LIKE', "%" . $request['name'] . "%");
                })
            )
            ->when(
                isset($request['email']) && !empty($request['email']),
                fn($q) => $q->where('leads.email', 'LIKE', "%" . $request['email'] . "%")
            )
            ->when(
                isset($request['phone']) && !empty($request['phone']),
                fn($q) => $q->where('leads.phone', 'LIKE', "%" . $request['phone'] . "%")
            )
            ->when(
                isset($request['status']) && $request['status'] != 0,
                fn($q) => $q->where('leads.status', $request['status'])
            )
            ->when(
                isset($request['status_id']) && $request['status_id'] != 0,
                fn($q) => $q->where('leads.status_id', $request['status_id'])
            )
            ->when(
                isset($request['group_id']) && $request['group_id'] != 0,
                fn($q) => $q->where('leads.group_id', $request['group_id'])
            )
            ->when(
                isset($request['source_id']) && $request['source_id'] != 0,
                fn($q) => $q->where('leads.source_id', $request['source_id'])
            )
            ->when(
                isset($request['start_date']) && !empty($request['start_date']),
                fn($q) => $q->whereDate('leads.created_at', '>=', $request['start_date'])
            )
            ->when(
                isset($request['end_date']) && !empty($request['end_date']),
                fn($q) => $q->whereDate('leads.created_at', '<=', $request['end_date'])
            )
            ->when(
                isset($request['assign_to']) && count($request['assign_to']) > 0,
                fn($q) => $q->whereHas(
                    'assignees',
                    fn($assigneeQuery) =>
                    $assigneeQuery->whereIn('users.id', $request['assign_to'])
                )
            )
            ->when(
                isset($request['assign_by']) && $request['assign_by'] != 0,
                fn($q) => $q->whereHas(
                    'assignedBy',
                    fn($assignByQuery) =>
                    $assignByQuery->where('users.id', $request['assign_by'])
                )
            );
    }

}
