<?php
namespace YnsInc\MakeRIS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {--controller=} {--subdir=} {--name=}';
    protected $description = 'Create a new service';

    public function handle()
    {
        $controller = $this->option('controller');
        $subdir = $this->option('subdir');
        $name = $this->option('name');

        if (!$controller && !$name) {
            $this->error('You must provide at least --controller or --name flag');
            return;
        }

        if ($this->hasOptionWithEmptyValue('subdir') || $this->hasOptionWithEmptyValue('controller') || $this->hasOptionWithEmptyValue('name')) {
            $this->error('Empty values are not accepted for the provided options.');
            return;
        }

        $className = $name ?: $controller . 'Service';
        $directory = $subdir ? $subdir . '/' : '';
        $namespace = $subdir ? '\\' . str_replace('/', '\\', $subdir) : '';

        $servicePath = app_path("Services/{$directory}{$className}.php");

        $this->createFile($servicePath, $this->getServiceContent($className, $namespace));

        $this->info("{$className} created successfully.");
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

    protected function getServiceContent($className, $namespace)
    {
        return <<<PHP
<?php

namespace App\Services{$namespace};

class {$className}
{
    // Service implementation
}
PHP;
    }
}
