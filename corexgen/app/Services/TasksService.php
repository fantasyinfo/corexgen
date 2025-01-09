<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\CategoryGroupTag;
use App\Models\Tasks;
use App\Repositories\TasksRepository;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class TasksService
{
    use CategoryGroupTagsFilter;
    use TenantFilter;


    protected $tasksRepository;

    private $tenantRoute;

    private $attachmentService;

    public function __construct(TasksRepository $tasksRepository, AttachmentService $attachmentService)
    {
        $this->tasksRepository = $tasksRepository;
        $this->attachmentService = $attachmentService;
        $this->tenantRoute = $this->getTenantRoute();
    }


    public function createTask(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {

            $validStatusID = $this->checkIsValidCGTID($validatedData['status_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['tasks_status'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['tasks']);

            if (!$validStatusID) {
                throw new \InvalidArgumentException("Failed to create lead beacuse invalid Stage ID ");
            }



            $task = Tasks::create($validatedData);

            $this->addAttachmentsIFProvideded($validatedData, $task);
            // assign  
            $this->assignTasksToUserIfProvided($validatedData, $task);
            return $task;
        });
    }


    public function updateTask(array $validatedData)
    {
        if (empty($validatedData['id'])) {
            throw new \InvalidArgumentException('Task ID is required for updating');
        }
        return DB::transaction(function () use ($validatedData) {

            $validStatusID = $this->checkIsValidCGTID($validatedData['status_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['tasks_status'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['tasks']);

            if (!$validStatusID) {
                throw new \InvalidArgumentException("Failed to create task beacuse invalid Stage ID ");
            }


            $task = Tasks::findOrFail($validatedData['id']);
            unset($validatedData['id']);


            $task->update($validatedData);

            $this->addAttachmentsIFProvideded($validatedData, $task);
            // assign  
            $this->assignTasksToUserIfProvided($validatedData, $task);
            return $task;
        });
    }

    public function addAttachmentsIFProvideded(array $validatedData, Tasks $task)
    {

        if (!empty($validatedData['files']) && is_array($validatedData['files'])) {


            $this->attachmentService->add($task, $validatedData);
        }
    }


    public function assignTasksToUserIfProvided(array $validatedData, Tasks $task)
    {
        if (!empty($validatedData['assign_to']) && is_array($validatedData['assign_to'])) {
            // Retrieve current assignees from the database
            $existingAssignees = $task->assignees()->pluck('task_user.user_id')->sort()->values()->toArray();
            $newAssignees = collect($validatedData['assign_to'])->sort()->values()->toArray();

            // Skip if assignees are identical - NO NEED TO CREATE AUDIT
            if ($existingAssignees === $newAssignees) {
                return;
            }

            // Prepare data for pivot table
            $companyId = Auth::user()->company_id;
            $assignToData = collect($validatedData['assign_to'])->mapWithKeys(function ($userId) use ($companyId) {
                return [
                    $userId => [
                        'company_id' => $companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ];
            })->toArray();

            // Sync assignments
            $task->assignees()->sync($assignToData);

        } else {
            // Handle detachment of assignees
            $existingAssignees = $task->assignees()->pluck('task_user.user_id')->toArray();

            if (empty($existingAssignees)) {
                return; // No existing assignees, skip detachment and logging
            }

            $task->assignees()->detach();


        }
    }


    public function getAllTasks(int $project_id = null)
    {
        if ($project_id == null) {
            return Tasks::with('assignees','stage:id,name,color')->where('company_id', Auth::user()->company_id)->get();
        }
        return Tasks::with('assignees','stage:id,name,color',)->where('company_id', Auth::user()->company_id)->where('project_id', $project_id)->get();
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
            ->editColumn('related_to', function ($task) {
                return TASKS_RELATED_TO['STATUS'][$task?->related_to];
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
        return View::make(getComponentsDirFilePath('dt-leads-stage'), [
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