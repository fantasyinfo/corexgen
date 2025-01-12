<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * creaet repository class MakeRepository
 */
class MakeRepository extends Command
{
    protected $signature = 'make:repository {name}';
    protected $description = 'Create a new repository class';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path("Repositories/{$name}.php");

        if ($this->files->exists($path)) {
            $this->error("Repository {$name} already exists!");
            return;
        }

        $stub = <<<EOT
<?php

namespace App\Repositories;

class {$name}
{
    // Your repository methods
}

EOT;

        $this->files->put($path, $stub);
        $this->info("Repository {$name} created successfully.");
    }
}
