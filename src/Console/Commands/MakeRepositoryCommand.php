<?php

namespace YnsInc\MakeRIS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {--model=} {--subdir=} {--name=}';
    protected $description = 'Create a new repository and interface';

    /**
     * Handle the command execution.
     */
    public function handle()
    {
        $model = $this->option('model');
        $subdir = $this->option('subdir');
        $name = $this->option('name');

        if (!$model && !$name) {
            $this->error('You must provide at least --model or --name flag');
            return;
        }

        if (
            $this->hasOptionWithEmptyValue('subdir') ||
            $this->hasOptionWithEmptyValue('model') ||
            $this->hasOptionWithEmptyValue('name')
        ) {
            $this->error('Empty values are not accepted for the provided options.');
            return;
        }

        $className = ucfirst($name ?: $model);
        $directory = $subdir ? $subdir . '/' : '';
        $namespace = $subdir ? "App\\Repositories\\$subdir" : 'App\\Repositories';
        $interfaceNamespace = $subdir ? "App\\Interfaces\\$subdir" : 'App\\Interfaces';

        $this->ensureBaseFilesExist();

        $this->createInterface($className, $interfaceNamespace, $directory);
        $this->createRepository($className, $namespace, $interfaceNamespace, $directory, $model);
    }

    /**
     * Ensure base interface and repository files exist.
     */
    protected function ensureBaseFilesExist()
    {
        $interfacePath = app_path('Interfaces/BaseInterface.php');
        $repositoryPath = app_path('Repositories/BaseRepository.php');

        if (!File::exists($interfacePath)) {
            File::put($interfacePath, $this->getBaseInterfaceContent());
        }

        if (!File::exists($repositoryPath)) {
            File::put($repositoryPath, $this->getBaseRepositoryContent());
        }
    }

    /**
     * Create an interface file if it does not exist.
     *
     * @param string $className
     * @param string $namespace
     * @param string $directory
     */
    protected function createInterface(string $className, string $namespace, string $directory)
    {
        $path = app_path("Interfaces/{$directory}{$className}Interface.php");
        $directoryPath = app_path('Interfaces' . ($directory ? "/{$directory}" : ''));

        // Ensure the directory exists before creating the file
        File::ensureDirectoryExists($directoryPath, 0755, true);

        if (!File::exists($path)) {
            $content = <<<EOD
<?php

namespace $namespace;

use App\Interfaces\BaseInterface;

interface {$className}Interface extends BaseInterface
{
}

EOD;
            File::put($path, $content);
        }
    }

    /**
     * Create a repository file if it does not exist.
     *
     * @param string $className
     * @param string $namespace
     * @param string $interfaceNamespace
     * @param string $directory
     * @param string|null $model
     */
    protected function createRepository(
        string $className,
        string $namespace,
        string $interfaceNamespace,
        string $directory,
        ?string $model
    ) {
        $path = app_path("Repositories/{$directory}{$className}Repository.php");
        $directoryPath = app_path('Repositories' . ($directory ? "/{$directory}" : ''));

        // Ensure the directory exists before creating the file
        File::ensureDirectoryExists($directoryPath, 0755, true);

        if (!File::exists($path)) {
            $content = <<<EOD
<?php

namespace $namespace;

use $interfaceNamespace\\{$className}Interface;
use App\Repositories\BaseRepository;
use App\Models\\{$className};

class {$className}Repository extends BaseRepository implements {$className}Interface
{
    /**
     * Constructor
     *
     * @param \App\Models\\{$className} \${$this->camelCase($className)} The model instance.
     */
    public function __construct({$className} \${$this->camelCase($className)})
    {
        parent::__construct(\${$this->camelCase($className)});
    }
}

EOD;
            File::put($path, $content);
        }
    }

    /**
     * Convert a string to camelCase.
     *
     * @param string $string
     * @return string
     */
    protected function camelCase(string $string): string
    {
        return lcfirst($string);
    }

    /**
     * Get the content for BaseInterface.php.
     *
     * @return string
     */
    protected function getBaseInterfaceContent(): string
    {
        return <<<EOD
<?php

namespace App\Interfaces;

/**
 * Interface BaseInterface
 *
 * Defines the contract for basic CRUD operations.
 */
interface BaseInterface
{
    /**
     * Retrieve all records.
     *
     * @return \Illuminate\Database\Eloquent\Collection A collection of model instances.
     */
    public function all(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Find a record by ID.
     *
     * @param int \$id The ID of the record.
     * @return mixed
     */
    public function find(int \$id): mixed;

    /**
     * Create a new record.
     *
     * @param array<mixed> \$data The data for the new record.
     * @return \Illuminate\Database\Eloquent\Model The created model instance.
     */
    public function create(array \$data): \Illuminate\Database\Eloquent\Model;

    /**
     * Update an existing record.
     *
     * @param int \$id The ID of the record to update.
     * @param array<mixed> \$data The updated data.
     * @return \Illuminate\Database\Eloquent\Model The updated model instance.
     */
    public function update(int \$id, array \$data): \Illuminate\Database\Eloquent\Model;

    /**
     * Delete a record by ID.
     *
     * @param int|array<mixed> \$ids The ID or an array of IDs to delete.
     * @return bool|null True if at least one record was deleted, false otherwise.
     */
    public function delete(int|array \$ids): ?bool;
}

EOD;
    }

    /**
     * Get the content for BaseRepository.php.
     *
     * @return string
     */
    protected function getBaseRepositoryContent(): string
    {
        return <<<EOD
<?php

namespace App\Repositories;

use App\Interfaces\BaseInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseRepository
 *
 * This class provides a base implementation of common database operations
 * for Eloquent models, following the repository pattern.
 */
class BaseRepository implements BaseInterface
{
    /**
     * @var Model The Eloquent model instance.
     */
    protected Model \$model;

    /**
     * BaseRepository constructor.
     *
     * @param Model \$model The model instance to be used.
     */
    public function __construct(Model \$model)
    {
        \$this->model = \$model;
    }

    /**
     * Retrieve all records.
     *
     * @return Collection A collection of model instances.
     */
    public function all(): Collection
    {
        return \$this->model->all();
    }

    /**
     * Find a record by ID.
     *
     * @param int \$id The ID of the record.
     * @return mixed
     */
    public function find(int \$id): mixed
    {
        return \$this->model->findOrFail(\$id);
    }

    /**
     * Create a new record.
     *
     * @param array<mixed> \$data The data for the new record.
     * @return Model The created model instance.
     */
    public function create(array \$data): Model
    {
        return \$this->model->create(\$data);
    }

    /**
     * Update an existing record.
     *
     * @param int \$id The ID of the record to update.
     * @param array<mixed> \$data The updated data.
     * @return Model The updated model instance.
     */
    public function update(int \$id, array \$data): Model
    {
        \$record = \$this->model->findOrFail(\$id);
        \$record->update(\$data);

        return \$record;
    }

    /**
     * Delete one or multiple records by ID.
     *
     * @param int|array<mixed> \$ids The ID or an array of IDs to delete.
     * @return bool True if at least one record was deleted, false otherwise.
     */
    public function delete(int|array \$ids): bool
    {
        return \$this->model->destroy(\$ids) > 0;
    }
}

EOD;
    }

    /**
     * Determine if a given option has an empty string as its value.
     *
     * @param string $option The name of the option to check.
     * @return bool True if the option exists and its value is an empty string, false otherwise.
     */
    protected function hasOptionWithEmptyValue(string $option): bool
    {
        $value = $this->option($option);
        return $value !== null && $value === '';
    }
}