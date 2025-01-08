<?php

namespace Database\Seeders;

use App\Models\CategoryGroupTag;
use App\Models\TaskUser;
use App\Services\ProjectService;
use App\Services\TasksService;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class TasksSeeder extends Seeder
{
    protected $taskService;
    protected $projectService;

    public function __construct(TasksService $taskService, ProjectService $projectService)
    {
        $this->taskService = $taskService;
        $this->projectService = $projectService;
    }

    public function run(): void
    {
        $faker = Faker::create();
        $loginUserId = 112;
        $companyId = 10;

        // Fetch statuses and ensure they are available
        $statusIds = CategoryGroupTag::where('type', CATEGORY_GROUP_TAGS_TYPES['KEY']['tasks_status'])
            ->where('relation_type', CATEGORY_GROUP_TAGS_RELATIONS['KEY']['tasks'])
            ->where('status', 'active')
            ->where('company_id', $companyId)
            ->pluck('id')
            ->toArray();

        if (empty($statusIds)) {
            $this->command->warn('No status IDs found. Seeder will not run.');
            return;
        }

        // Fetch user IDs
        $userIds = DB::table('users')->where('company_id', $companyId)->pluck('id')->toArray();
        if (empty($userIds)) {
            $this->command->warn('No user IDs found. Seeder will not run.');
            return;
        }

        // Fetch project IDs
        $projectIds = $this->projectService->getAllProjects()->pluck('id')->toArray();
        if (!$projectIds) {
            $this->command->warn('No projects found. Seeder will not run.');
            return;
        }

        for ($i = 0; $i <= 100; $i++) {
            $startDate = $faker->date();
            $dueDate = $faker->dateTimeBetween($startDate, '+1 month')->format('Y-m-d');

            $lead = [
                'title' => $faker->jobTitle,
                'billable' => [0, 1][array_rand([0, 1])],
                'start_date' => $startDate,
                'due_date' => $dueDate,
                'related_to' => TASKS_RELATED_TO['TABLE_STATUS'][array_rand(TASKS_RELATED_TO['TABLE_STATUS'])],
                'project_id' => $projectIds[array_rand($projectIds)],
                'hourly_rate' => $faker->numberBetween(1111, 9999),
                'description' => $faker->text(300),
                'priority' => ['Low', 'Medium', 'High', 'Urgent'][array_rand(['Low', 'Medium', 'High', 'Urgent'])],
                'assign_by' => $loginUserId,
                'company_id' => $companyId,
                'status_id' => $statusIds[array_rand($statusIds)],
                'updated_at' => now(),
                'created_at' => now(),
            ];

            $taskId = DB::table('tasks')->insertGetId($lead);

            $assignedUsers = (array) array_rand($userIds, mt_rand(1, 3));
            foreach ($assignedUsers as $userKey) {
                TaskUser::create([
                    'task_id' => $taskId,
                    'user_id' => $userIds[$userKey],
                    'company_id' => $companyId,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
        }
    }
}
