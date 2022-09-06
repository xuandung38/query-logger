<?php

namespace Hxd\QueryLogger;

use Illuminate\Support\ServiceProvider;

class QueryLoggerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/query-logger.php' => config_path('query-logger.php'),
            ], 'query-logger-config');
        }

        $this->app->booted(function ($app) {
            $app[QueryLoggerInterface::class]->boot();
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/query-logger.php', 'query-logger');

        // Binding QueryLogger service, make the service can be extensible.
        $this->app->singleton(QueryLoggerInterface::class, QueryLogger::class);
    }
}
