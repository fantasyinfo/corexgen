<?php

namespace Database\Seeders;

use App\Models\LeadUser;
use App\Services\LeadsService;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class LeadsSeeder extends Seeder
{
    protected LeadsService $leadsService;

    public function __construct(LeadsService $leadsService)
    {
        $this->leadsService = $leadsService;
    }

    public function run(): void
    {
        $faker = Faker::create();
        $leads = [];
        $type = ['Individual', 'Company'];

        $loginUserId = 45; // Default user ID
        $companyId = 14;    // Default company ID

        // Fetch all IDs for groups, sources, statuses, and users
        $groupIds = $this->leadsService->getLeadsGroups()->pluck('id')->toArray();
        $sourceIds = $this->leadsService->getLeadsSources()->pluck('id')->toArray();
        $statusIds = $this->leadsService->getLeadsStatus()->pluck('id')->toArray();
        $userIds = DB::table('users')->where('company_id', $companyId)->pluck('id')->toArray(); // Fetch user IDs

        for ($i = 0; $i <= 100; $i++) {
            $typeTo = $type[array_rand($type)];
            $lead = [
                'type' => $typeTo,
                'company_name' => $typeTo == 'Company' ? $faker->company : null,
                'title' => $faker->jobTitle,
                'value' => $faker->numberBetween(1111, 9999),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->safeEmail,
                'phone' => $faker->phoneNumber,
                'last_contacted_date' => $faker->date(),
                'last_activity_date' => $faker->date(),
                'priority' => ['Low', 'Medium', 'High'][array_rand(['Low', 'Medium', 'High'])],
                'preferred_contact_method' => ['Email', 'Phone', 'In-Person'][array_rand(['Email', 'Phone', 'In-Person'])],
                'score' => $faker->numberBetween(1, 100),
                'follow_up_date' => $faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
                'is_converted' => $faker->boolean,
                'status' => CRM_STATUS_TYPES['LEADS']['STATUS']['ACTIVE'],
                'updated_by' => $loginUserId,
                'created_by' => $loginUserId,
                'assign_by' => $loginUserId,
                'company_id' => $companyId,
                'group_id' => $groupIds[array_rand($groupIds)],
                'source_id' => $sourceIds[array_rand($sourceIds)],
                'status_id' => $statusIds[array_rand($statusIds)],
            ];

            // Insert lead and get the inserted ID
            $leadId = DB::table('leads')->insertGetId($lead);

            // Sync the lead assignments
            $assignedUsers = array_rand($userIds, mt_rand(1, 3)); // Assign 1-3 random users
            if (!is_array($assignedUsers)) {
                $assignedUsers = [$assignedUsers];
            }

            foreach ($assignedUsers as $userKey) {
                LeadUser::create([
                    'lead_id' => $leadId,
                    'user_id' => $userIds[$userKey],
                    'company_id' => $companyId,
                ]);
            }
        }
    }
}
