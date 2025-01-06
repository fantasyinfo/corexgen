<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectRepository
{
    // Your repository methods
    public function getProjectsQuery($request)
    {

        $wantCurrentUserItems = filter_var($request->input('current_user'), FILTER_VALIDATE_BOOLEAN);

        $query = Project::query()->select('projects.*')
            ->with([
                'client',
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

    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('search'),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $searchTerm = strtolower($request->search['value']);
                    $subQuery->where('projects.billing_type', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('projects.title', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('projects.client_id', '=', "%{$searchTerm}%")
                        ->orWhere('projects.created_at', 'LIKE', "%{$searchTerm}%")
                        ;
                })
            )
            ->when(
                $request->filled('title'),
                fn($q) => $q->where('projects.title', 'LIKE', "%{$request->title}%")
            )
            ->when(
                $request->filled('client_id') && $request->client_id != 0,
                fn($q) => $q->where('projects.client_id', '=', "$request->client_id")
            )
            ->when(
                $request->filled('billing_type') && $request->billing_type != 0,
                fn($q) => $q->where('projects.billing_type', $request->billing_type)
            )
            ->when(
                $request->filled('status') && $request->status != 0,
                fn($q) => $q->where('projects.status', $request->status)
            )

            ->when(
                $request->filled('start_date'),
                fn($q) => $q->whereDate('projects.created_at', '>=', $request->start_date)
            )
            ->when(
                $request->filled('due_date'),
                fn($q) => $q->whereDate('projects.due_date', '=', $request->due_date)
            )
            ->when(
                $request->filled('assign_to') && count($request->assign_to) != 0,
                fn($q) => $q->whereHas(
                    'assignees',
                    fn($assigneeQuery) =>
                    $assigneeQuery->whereIn('users.id', $request->assign_to)
                )
            );
    }


}
