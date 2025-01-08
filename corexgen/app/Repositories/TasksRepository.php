<?php

namespace App\Repositories;


use App\Models\Tasks;
use Illuminate\Support\Facades\Auth;

class TasksRepository
{
    // Your repository methods

    public function getTasksQuery($request)
    {

        $wantCurrentUserItems = filter_var($request->input('current_user'), FILTER_VALIDATE_BOOLEAN);

        $query = Tasks::query()
            ->select([
                'tasks.id',
                'tasks.priority',
                'tasks.billable',
                'tasks.start_date',
                'tasks.related_to',
                'tasks.due_date',
                'tasks.title',
                'tasks.created_at',
                'tasks.status_id',
                'tasks.assign_by'
            ])
            ->with([
                'project',
                'stage:id,name,color',
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
        //return $query;
    }

    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('search'),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $searchTerm = strtolower($request->search['value']);
                    $subQuery->where('tasks.hourly_rate', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('tasks.title', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas(
                            'stage',
                            fn($stageQuery) =>
                            $stageQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        )
                        ->orWhere('tasks.created_at', 'LIKE', "%{$searchTerm}%");
                })
            )
            ->when(
                $request->filled('title'),
                fn($q) => $q->where('tasks.title', 'LIKE', "%{$request->title}%")
            )
            ->when(
                $request->filled('status_id') && $request->status_id != 0,
                fn($q) => $q->where('tasks.status_id', $request->status_id)
            )
            ->when(
                $request->filled('project_id') && $request->project_id != 0,
                fn($q) => $q->where('tasks.project_id', $request->project_id)
            )
            ->when(
                $request->filled('related_to') && $request->related_to != 0,
                fn($q) => $q->where('tasks.related_to', $request->related_to)
            )
            ->when(
                $request->filled('start_date'),
                fn($q) => $q->whereDate('tasks.start_date', '>=', $request->start_date)
            )
            ->when(
                $request->filled('due_date'),
                fn($q) => $q->whereDate('tasks.due_date', '<=', $request->due_date)
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


    public function getKanbanLoad($request)
    {
        $wantCurrentUserItems = false;
        if (isset($request['query']['current_user']) && $request['query']['current_user'] == true) {
            $wantCurrentUserItems = filter_var($request['query']['current_user'], FILTER_VALIDATE_BOOLEAN);
        }

        $query = Tasks::query()
            ->select([
                'tasks.id',
                'tasks.priority',
                'tasks.billable',
                'tasks.start_date',
                'tasks.related_to',
                'tasks.due_date',
                'tasks.title',
                'tasks.created_at',
                'tasks.status_id',
                'tasks.assign_by'
            ])
            ->with([
                'project',
                'attachments',
                'stage:id,name,color',
                'assignedBy:id,name',
                'assignees' => fn($q) => $q
                    ->select(['users.id', 'users.name'])
                    ->withOnly([])
            ]);

        if ($wantCurrentUserItems === true) {
            $query->whereHas('assignees', function ($query) {
                $query->where('users.id', Auth::id());
            });
        }

        //return $this->applyKanbanFilters($query, $request['filters']);
        return $query;
    }

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
