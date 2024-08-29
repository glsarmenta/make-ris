<?php

namespace YnsInc\MakeRIS;

use Illuminate\Support\ServiceProvider;

class MakeRISServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \YnsInc\MakeRIS\Console\Commands\MakeRepositoryCommand::class,
                \YnsInc\MakeRIS\Console\Commands\MakeServiceCommand::class
            ]);
        }
    }

    public function register()
    {
        //
    }
}