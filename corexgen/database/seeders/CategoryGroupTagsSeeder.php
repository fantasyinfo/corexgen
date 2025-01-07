<?php

namespace Database\Seeders;

use App\Models\CategoryGroupTag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryGroupTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $companyid = 10;
        // first delete all

        CategoryGroupTag::where('company_id', $companyid)->delete();

        // then create
        $insertArray = [];
        $clientsCategory = [
            'warning' => 'VIP',
            'info' => 'Normal',
            'success' => 'High Budget',
            'primary' => 'Low Budget'
        ];

        foreach ($clientsCategory as $color => $cc) {
            $insertArray[] = [
                'name' => $cc,
                'color' => $color, // Assign unique color
                'relation_type' => CATEGORY_GROUP_TAGS_RELATIONS['KEY']['clients'],
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['categories'],
                'status' => 'active',
                'company_id' => $companyid,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }


        // leads groups
        $leadsGroups = [
            'danger' => 'Hot',
            'warning' => 'Warm',
            'light' => 'Cold'
        ];

        foreach ($leadsGroups as $color => $cc) {
            $insertArray[] = [
                'name' => $cc,
                'color' => $color,
                'relation_type' => CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads'],
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_groups'],
                'status' => 'active',
                'company_id' => $companyid,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }


        // leads status
        $leadsStatus = [
            'info' => 'New',
            'secondary' => 'Qualified',
            'dark' => 'Contacted',
            'primary' => 'Proposal Sent',
            'success' => 'Converted',
            'danger' => 'Disqualified',
        ];

        foreach ($leadsStatus as $color => $cc) {
            $insertArray[] = [
                'name' => $cc,
                'color' => $color,
                'relation_type' => CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads'],
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'],
                'status' => 'active',
                'company_id' => $companyid,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // leads sources
        $leadsSources = [
            'info' => 'Social Media',
            'primary' => 'Website',
            'dark' => 'Ads',
            'secondary' => 'Referral',
            'light' => 'Other'
        ];

        foreach ($leadsSources as $color => $cc) {
            $insertArray[] = [
                'name' => $cc,
                'color' => $color,
                'relation_type' => CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads'],
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_sources'],
                'status' => 'active',
                'company_id' => $companyid,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // products categories

        $productsCategories = [
            'warning' => 'Electronics',
            'info' => 'Cloths',
            'success' => 'Development',
            'primary' => 'Designing'
        ];

        foreach ($productsCategories as $color => $cc) {
            $insertArray[] = [
                'name' => $cc,
                'color' => $color, // Assign unique color
                'relation_type' => CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services'],
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['products_categories'],
                'status' => 'active',
                'company_id' => $companyid,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // products taxes

        $productTaxes = [
            'dark' => '0%',
            'warning' => '5%',
            'info' => '12%',
            'success' => '18%',
            'primary' => '28%'
        ];

        foreach ($productTaxes as $color => $cc) {
            $insertArray[] = [
                'name' => $cc,
                'color' => $color, // Assign unique color
                'relation_type' => CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services'],
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['products_taxs'],
                'status' => 'active',
                'company_id' => $companyid,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }


        // tasks status
        $tasksStatus = [
            'info' => 'New',
            'secondary' => 'In Progress',
            'dark' => 'Testing',
            'primary' => 'Awating Feedback',
            'success' => 'Completed',
            'danger' => 'Issue',
        ];

        foreach ($tasksStatus as $color => $cc) {
            $insertArray[] = [
                'name' => $cc,
                'color' => $color,
                'relation_type' => CATEGORY_GROUP_TAGS_RELATIONS['KEY']['tasks'],
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['tasks_status'],
                'status' => 'active',
                'company_id' => $companyid,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        CategoryGroupTag::insert($insertArray);

    }
}
