<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use OwenIt\Auditing\Auditable; // Import the Auditable trait directly

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable auditing globally
        Auditable::disableAuditing();

        // Call all seeders
        $this->call([
            CountrySeeder::class,
            CRMPermissionsSeeder::class,
            CRMMenuSeeder::class,
            CRMRoleSeeder::class,
            CRMSettingsSeeder::class,
            PlansSeeder::class,
            PaymentGatewaySeeder::class,
            FrontendSeeder::class,
        ]);

        // Re-enable auditing globally
        Auditable::enableAuditing();
    }
}
