<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\CategoryGroupTag;
use App\Repositories\TasksRepository;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class TasksService
{
    use CategoryGroupTagsFilter;
    use TenantFilter;


    protected $tasksRepository;

    private $tenantRoute;

    public function __construct(TasksRepository $tasksRepository)
    {
        $this->tasksRepository = $tasksRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }
 
    public function getDatatablesResponse($request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->tasksRepository->getTasksQuery($request);
        $query = $this->applyTenantFilter($query, 'tasks');

        $module = PANEL_MODULES[$this->getPanelModule()]['tasks'];
        $umodule = PANEL_MODULES[$this->getPanelModule()]['users'];

        $stages = $this->getTasksStatus();

        return DataTables::of($query)
            ->addColumn('actions', function ($task) {
                return $this->renderActionsColumn($task);
            })
            ->editColumn('created_at', function ($task) {
                return formatDateTime($task?->created_at);
            })
            ->editColumn('title', function ($task) use ($module) {
                return "<a  class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $task->id) . "' target='_blank'>$task->title</a>";
            })
            ->editColumn('stage', function ($task) use ($stages) {
                // return "<span class='badge badge-pill bg-" . $task->stage->color . "'>{$task->stage->name}</span>";
                return $this->renderStageColumn($task, $stages);
            })
            ->editColumn('assign_to', function ($task) use ($umodule) {
                $assign_to = "";
                foreach ($task->assignees as $user) {
                    $assign_to .= '<a href="' . route($this->tenantRoute . $umodule . '.view', ['id' => $user->id]) . '">';
                    $assign_to .= '<img src="' . asset(
                        'storage/' . ($user->profile_photo_path ?? 'avatars/default.webp')
                    ) . '" alt="' . $user->name . '" title="' . $user->name . '" style="width:40px; height:40px; border-radius:50%;" />';
                    $assign_to .= '</a>';
                }
                return $assign_to;
            })

            ->rawColumns(['actions', 'assign_to', 'title', 'stage', 'name']) // Include any HTML columns
            ->make(true);
    }



    protected function renderActionsColumn($task)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('TASKS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
            'id' => $task->id
        ])->render();
    }

    protected function renderStageColumn($task, $stages)
    {
        return View::make(getComponentsDirFilePath('dt-tasks-stage'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('TASKS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
            'id' => $task->id,
            'status' => [
                'current_status' => $task->status_id,
                'available_status' => $stages
            ]
        ])->render();
    }

    public function getKanbanBoardStages($request)
    {
        $query = CategoryGroupTag::where('type', CATEGORY_GROUP_TAGS_TYPES['KEY']['tasks_status'])
            ->where('relation_type', CATEGORY_GROUP_TAGS_RELATIONS['KEY']['tasks']);

        $query = $this->applyTenantFilter($query);

        return $query->select(['id', 'name', 'color'])->get();

    }

    public function getKanbanLoad($request)
    {
        $query = $this->tasksRepository->getKanbanLoad($request);
        $query = $this->applyTenantFilter($query, 'tasks');
        return $query->get()->groupBy('stage_name');
    }

    public function getTasksStatus()
    {
        $leadsGroups = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['tasks_status'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['tasks']);
        $leadsGroups = $this->applyTenantFilter($leadsGroups);
        return $leadsGroups->get();
    }
}