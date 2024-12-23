<?php

namespace Database\Seeders;

use App\Models\Plans;
use App\Models\PlansFeatures;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            'free' => [
                'name' => 'Free',
                'desc' => 'For Learning',
                'price' => 00,
                'offer_price' => 00,
                'billing_cycle' => PLANS_BILLING_CYCLES['BILLINGS']['1 MONTH'],
                'value' => 2
            ],
            'gold' => [
                'name' => 'Gold',
                'desc' => 'Recommended for companies',
                'price' => 399.99,
                'offer_price' => 249.99,
                'billing_cycle' => PLANS_BILLING_CYCLES['BILLINGS']['1 MONTH'],
                'value' => 30
            ],
            'diamond' => [
                'name' => 'Diamond Unlimited',
                'desc' => 'For Bigger Brands',
                'price' => 1299,
                'offer_price' => 999.99,
                'billing_cycle' => PLANS_BILLING_CYCLES['BILLINGS']['1 YEAR'],
                'value' => -1
            ]
        ];

        // delete all exiting first
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Plans::truncate();
        PlansFeatures::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
        DB::beginTransaction();
        try {
            foreach ($plans as $planKey => $plan) {
                $value = $plan['value'];
                unset($plan['value']);
                $createdPlan = Plans::create($plan);
                $planId = $createdPlan->id;
    
                foreach (PLANS_FEATURES as $md) {
                    PlansFeatures::create([
                        'plan_id' => $planId,
                        'module_name' => strtolower($md),
                        'value' => $value
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Plan seeding failed: ' . $e->getMessage());
        }
    }
}
