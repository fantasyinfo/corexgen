<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\Project;
use App\Models\User;
use App\Repositories\ProjectRepository;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\MediaTrait;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class ProjectService
{

    use TenantFilter;
    use MediaTrait;
    use CategoryGroupTagsFilter;


    protected $projectRepository;

    private $tenantRoute;


    private $clientService;

    public function __construct(ProjectRepository $projectRepository, ClientService $clientService)
    {
        $this->projectRepository = $projectRepository;
        $this->clientService = $clientService;
        $this->tenantRoute = $this->getTenantRoute();
    }

    /**
     * create project
     */
    public function createProject(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {

            $project = Project::create($validatedData);

            // assign projects 
            $this->assignprojectsToUserIfProvided($validatedData, $project);

            return $project;
        });
    }

    /**
     * update project
     */
    public function updateProject(array $validatedData)
    {
        // Validate that company ID is provided
        if (empty($validatedData['id'])) {
            throw new \InvalidArgumentException('Project ID is required for updating');
        }

        return DB::transaction(function () use ($validatedData) {

            // Retrieve the existing client
            $project = Project::findOrFail($validatedData['id']);

            unset($validatedData['id']);

            $project->update($validatedData);

            // assign leads 
            $this->assignprojectsToUserIfProvided($validatedData, $project);

            return $project;
        });
    }

    /**
     * assing projects to users project
     */
    public function assignprojectsToUserIfProvided(array $validatedData, Project $project)
    {
        if (!empty($validatedData['assign_to']) && is_array($validatedData['assign_to'])) {
            // Retrieve current assignees from the database
            $existingAssignees = $project->assignees()->pluck('project_user.user_id')->sort()->values()->toArray();
            $newAssignees = collect($validatedData['assign_to'])->sort()->values()->toArray();

         // Find users to add and remove
         $usersToAdd = array_diff($newAssignees, $existingAssignees);
         $usersToRemove = array_diff($existingAssignees, $newAssignees);

            // Prepare data for pivot table
            $companyId = Auth::user()->company_id;
            $assignToData = collect($usersToAdd)->mapWithKeys(function ($userId) use ($companyId) {
                return [
                    $userId => [
                        'company_id' => $companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ];
            })->toArray();

 
            // Add new users
            if (!empty($usersToAdd)) {

                $project->assignees()->attach($assignToData);
        
                // Send emails to new users
                foreach ($usersToAdd as $userId) {
                    // Trigger email logic here
                    $this->sendEmailToUser($userId, $project);
                }
            }

            // Remove users who are no longer assigned
            if (!empty($usersToRemove)) {
      
                $project->assignees()->detach($usersToRemove);
            }

          

        } else {
            // Handle detachment of assignees
            $existingAssignees = $project->assignees()->pluck('project_user.user_id')->toArray();

            if (!empty($existingAssignees)) {
                $project->assignees()->detach();
            }


        }
    }


      // Example email sending logic
      private function sendEmailToUser($userId, Project $project)
      {
          // Replace with your email sending logic
          $user = User::find($userId);
          //todo: send email to new assingee users 
        //   info('new users to project', $user->toArray());
  
      }


    /**
     * get all projects
     */
    public function getAllProjects()
    {
        return $this->applyTenantFilter(Project::where('status', CRM_STATUS_TYPES['PROJECTS']['STATUS']['ACTIVE']))->get();
    }

    /**
     * get project by user
     */
    public function getProjectsByUser(int $user_id)
    {
        // Get leads assigned to the given user
        $leads = Project::whereHas('assignees', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->with('assignees')->get();

        // Apply tenant filter (ensure this function modifies or filters the results as intended)
        return $this->applyTenantFilter($leads);
    }

    /**
     * get dt response of projects
     */
    public function getDatatablesResponse($request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->projectRepository->getProjectsQuery($request);
        $query = $this->applyTenantFilter($query, 'projects');

        $module = PANEL_MODULES[$this->getPanelModule()]['projects'];
        $cmodule = PANEL_MODULES[$this->getPanelModule()]['clients'];
        $umodule = PANEL_MODULES[$this->getPanelModule()]['users'];




        return DataTables::of($query)
            ->addColumn('actions', function ($project) {
                return $this->renderActionsColumn($project);
            })
            ->editColumn('created_at', function ($project) {
                return formatDateTime($project?->created_at);
            })
            ->editColumn('title', function ($project) use ($module) {
                return "<a  class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $project->id) . "' target='_blank'>$project->title</a>";
            })
            ->editColumn('client_name', function ($project) use ($cmodule) {
                $client_name = "";
                if ($project?->client?->type == 'Individual') {
                    $client_name = $project?->client?->first_name . " " . $project?->client?->last_name . "(Individual)";
                } else {
                    $client_name = $project?->client?->company_name . " " . "(Company)";
                }
                return "<a  
                class='dt-link'  
               
                href='" . route($this->tenantRoute . $cmodule . '.view', $project?->client->id) . "' target='_blank'>$client_name</a>";
            })
            ->editColumn('assign_to', function ($project) use ($umodule) {
                $assign_to = "";
                foreach ($project->assignees as $user) {
                    $assign_to .= '<a 
                    
                    href="' . route($this->tenantRoute . $umodule . '.view', ['id' => $user->id]) . '">';
                    $assign_to .= '<img data-toggle="tooltip" src="' . asset(
                        'storage/' . ($user->profile_photo_path ?? 'avatars/default.webp')
                    ) . '" alt="' . $user->name . '" title="' . $user->name . '" style="width:40px; height:40px; border-radius:50%;" />';
                    $assign_to .= '</a>';
                }
                return $assign_to;
            })
            ->editColumn('status', function ($project) {
                return $this->renderStatusColumn($project);
            })
            ->rawColumns(['actions', 'client_name', 'assign_to', 'status', 'title', 'name']) // Include any HTML columns
            ->make(true);
    }


    /**
     *  render action col
     */
    protected function renderActionsColumn($project)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('PROJECTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'id' => $project->id
        ])->render();
    }

    /**
     *  render status col
     */
    protected function renderStatusColumn($project)
    {
        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('PROJECTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'id' => $project->id,
            'status' => [
                'current_status' => $project->status,
                'available_status' => CRM_STATUS_TYPES['PROJECTS']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['PROJECTS']['BT_CLASSES'],
            ]
        ])->render();
    }


}
