<?php

namespace App\Repositories;


use App\Models\Tasks;
use Illuminate\Support\Facades\Auth;

class TasksRepository
{
    // Your repository methods

    /**
     * get tasks lists query
     */
    public function getTasksQuery($request)
    {

        $wantCurrentUserItems = filter_var($request->input('current_user'), FILTER_VALIDATE_BOOLEAN);

        $query = Tasks::query()->latest()
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

    /**
     * get tasks lists query filtes
     */
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
                $request->filled('status_id') && $request->project_id != 0,
                fn($q) => $q->where('tasks.status_id', $request->project_id)
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


    /**
     * get kanban load lists query
     */
    public function getKanbanLoad($request)
    {
        $wantCurrentUserItems = false;
        if (isset($request['query']['current_user']) && $request['query']['current_user'] == true) {
            $wantCurrentUserItems = filter_var($request['query']['current_user'], FILTER_VALIDATE_BOOLEAN);
        }

        $query = Tasks::query()->latest()
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

        return $this->applyKanbanFilters($query, $request['filters']);
        //return $query;
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
                isset($request['title']) && !empty($request['title']),
                fn($q) => $q->where('tasks.title', 'LIKE', "%{$request['title']}%")
            )->when(
                isset($request['status_id']) && $request['status_id'] != 0,
                fn($q) => $q->where('tasks.status_id', $request['status_id'])
            )
            ->when(
                isset($request['project_id']) && $request['project_id'] != 0,
                fn($q) => $q->where('tasks.project_id', $request['project_id'])
            )
            ->when(
                isset($request['related_to']) && $request['related_to'] != 0,
                fn($q) => $q->where('tasks.related_to', $request['related_to'])
            )
            ->when(
                isset($request['start_date']),
                fn($q) => $q->whereDate('tasks.start_date', '>=', $request['start_date'])
            )
            ->when(
                isset($request['due_date']),
                fn($q) => $q->whereDate('tasks.due_date', '<=', $request['due_date'])
            )
            ->when(
                isset($request['assign_to']) && count($request['assign_to']) != 0,
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
            )
        ;
    }

}
