<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\ServeCommand;

class DevelopmentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->configureServeCommand();
        }
    }

    /**
     * Configura el comando `serve` para que siempre escuche en todas las interfaces.
     */
    protected function configureServeCommand(): void
    {
        $this->app->resolving(ServeCommand::class, function ($command) {
            $command->getDefinition()->getOption('host')->setDefault(config('server.host'));
            $command->getDefinition()->getOption('port')->setDefault(config('server.port'));
            return $command;
        });
    }
}