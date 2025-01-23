<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDemoDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the demo database to its initial state';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $module = getModule();// Determine the module

        // Determine the SQL file path based on the module
        $sqlFilePath = match ($module) {
            'saas' => storage_path('app/demo_saas_database.sql'),
            'company' => storage_path('app/demo_company_database.sql'),
            default => null,
        };

        if (!$sqlFilePath || !file_exists($sqlFilePath)) {
            $this->error('SQL file not found for module: ' . $module);
            return 1;
        }

        $this->info('Resetting the database for module: ' . $module);

        // Truncate or drop all existing tables
        $this->clearDatabase();

        // Import the SQL file
        DB::unprepared(file_get_contents($sqlFilePath));
        $this->info('Demo database for "' . $module . '" module has been reset successfully.');

        return 0;
    }

  

    /**
     * Clear the database by truncating or dropping all tables.
     *
     * @return void
     */
    private function clearDatabase()
    {
        // Get all table names dynamically without relying on property names
        $tables = DB::select('SHOW TABLES');
        $dbKey = array_key_first((array)$tables[0]); // Dynamically fetch the key name

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        // Drop all tables
        foreach ($tables as $table) {
            $tableName = $table->$dbKey;
            Schema::drop($tableName); // Drops the table
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $this->info('Database cleared successfully.');
    }
}
