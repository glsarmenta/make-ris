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

        $className = $name ?: $model;
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
    public function get();
    public function all();
    public function create(array \$data);
    public function update(\$id, array \$data);
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
            File::put($providerPath, $providerContent );
        }

        $binding = "\$this->app->bind(\\App\\Interfaces{$namespace}\\{$className}Interface::class, \\App\\Repositories{$namespace}\\{$className}Repository::class);";
        $providerContent = File::get($providerPath);

        if (strpos($providerContent, $binding) === false) {
            $providerContent = str_replace('// Register bindings here', "// Register bindings here\n        {$binding}", $providerContent);
            File::put($providerPath, $providerContent . PHP_EOL);
            $this->info("Bound {$className}Interface to {$className}Repository in RepositoryServiceProvider.");
        }
    }
}
