<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CRMPermissionsSeeder::class,
            CRMMenuSeeder::class,
            CRMRoleSeeder::class,
            CRMSettingsSeeder::class,
            PlansSeeder::class,
            PaymentGatewaySeeder::class
        ]);
    }
}
