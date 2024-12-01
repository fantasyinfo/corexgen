<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path("Services/{$name}.php");

        if (file_exists($path)) {
            $this->error("Service {$name} already exists!");
            return;
        }

        // Ensure Services directory exists
        if (!is_dir(app_path('Services'))) {
            mkdir(app_path('Services'));
        }

        // Create service class
        $stub = <<<EOT
        <?php

        namespace App\Services;

        class {$name}
        {
            public function handle()
            {
                // Add your logic here
            }
        }
        EOT;

        file_put_contents($path, $stub);

        $this->info("Service {$name} created successfully!");
    }
}
