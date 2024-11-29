<?php
namespace YnsInc\MakeRIS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {--model=} {--subdir=} {--name=}';
    protected $description = 'Create a new repository and interface';

    public function handle()
    {
        $model = $this->option('model');
        $subdir = $this->option('subdir');
        $name = $this->option('name');

        if (!$model && !$name) {
            $this->error('You must provide at least --model or --name flag');
            return;
        }

        if ($this->hasOptionWithEmptyValue('subdir') || $this->hasOptionWithEmptyValue('model') || $this->hasOptionWithEmptyValue('name')) {
            $this->error('Empty values are not accepted for the provided options.');
            return;
        }

        $className = ucfirst($name ?: $model);
        $directory = $subdir ? $subdir . '/' : '';
        $namespace = $subdir ? '\\' . str_replace('/', '\\', $subdir) : '';

        $interfacePath = app_path("Interfaces/{$directory}{$className}Interface.php");
        $repositoryPath = app_path("Repositories/{$directory}{$className}Repository.php");

        $this->createFile($interfacePath, $this->getInterfaceContent($className, $namespace));
        $this->createFile($repositoryPath, $this->getRepositoryContent($className, $namespace));
        
        $this->bindRepositoryToInterface($className, $namespace);

        $this->info("{$className}Interface and {$className}Repository created successfully.");
    }

    protected function createFile($path, $content)
    {
        if (File::exists($path)) {
            if (!$this->confirm("The file {$path} already exists. Do you want to overwrite it?")) {
                $this->info("Skipped: {$path}");
                return;
            }
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content . PHP_EOL);
        File::chmod($path, 0755); // Set automatic permissions
        $this->info("Created: {$path}");
    }

    protected function hasOptionWithEmptyValue($option)
    {
        $value = $this->option($option);
        return $value !== null && $value === '';
    }

    protected function getInterfaceContent($className, $namespace)
    {
        return <<<PHP
<?php

namespace App\Interfaces{$namespace};

interface {$className}Interface
{
    /**
     * Retrieve a single resource.
     *
     * @return mixed The resource object or data.
     */
    public function get();

    /**
     * Retrieve all resources.
     *
     * @return array An array containing all resource objects or data.
     */
    public function all();

    /**
     * Create a new resource with the provided data.
     *
     * @param array \$data The data for creating the resource.
     * @return mixed The created resource object or status.
     */
    public function create(array \$data);

    /**
     * Update an existing resource identified by the provided ID with the given data.
     *
     * @param mixed \$id The ID of the resource to be updated.
     * @param array \$data The updated data for the resource.
     * @return mixed The updated resource object or status.
     */
    public function update(\$id, array \$data);

    /**
     * Delete a resource identified by the provided ID.
     *
     * @param mixed \$id The ID of the resource to be deleted.
     * @return mixed The status of the deletion or result.
     */
    public function delete(\$id);
}
PHP;
    }

    protected function getRepositoryContent($className, $namespace)
    {
        return <<<PHP
<?php

namespace App\Repositories{$namespace};

use App\Interfaces{$namespace}\\{$className}Interface;

class {$className}Repository implements {$className}Interface
{
    /**
     * Retrieve a specific record.
     *
     * @return mixed
     */
    public function get()
    {
        // Implement get method
    }

    /**
     * Retrieve all records.
     *
     * @return mixed
     */
    public function all()
    {
        // Implement all method
    }

    /**
     * Create a new record.
     *
     * @param array \$data
     * @return mixed
     */
    public function create(array \$data)
    {
        // Implement create method
    }

    /**
     * Update an existing record.
     *
     * @param int \$id
     * @param array \$data
     * @return mixed
     */
    public function update(\$id, array \$data)
    {
        // Implement update method
    }

    /**
     * Delete a record.
     *
     * @param int \$id
     * @return mixed
     */
    public function delete(\$id)
    {
        // Implement delete method
    }
}
PHP;
    }

    protected function bindRepositoryToInterface($className, $namespace)
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        if (!File::exists($providerPath)) {
            $providerContent = <<<PHP
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Register bindings here
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        //
    }
}
PHP;
            File::put($providerPath, $providerContent);
        }

        $providerBinding = "\\App\\Interfaces{$namespace}\\{$className}Interface::class, \\App\\Repositories{$namespace}\\{$className}Repository::class";

        // Add to bootstrap/providers.php in Laravel 11
        $providersPath = base_path('bootstrap/providers.php');
        if (File::exists($providersPath)) {
            $content = File::get($providersPath);
            if (strpos($content, $providerBinding) === false) {
                $content = preg_replace(
                    '/return \\[(.*?)/s',
                    "return [\n    $providerBinding,\n\$1",
                    $content
                );
                File::put($providersPath, $content);
                $this->info("Bound {$className}Interface to {$className}Repository in bootstrap/providers.php.");
            }
        } else {
            $this->error('bootstrap/providers.php not found.');
        }
    }
}
