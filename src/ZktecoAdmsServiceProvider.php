<?php

namespace Rithy\ZktecoAdms;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Rithy\ZktecoAdms\Console\Commands\SyncZktecoAttendanceCommand;

class ZktecoAdmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/zkteco-adms.php', 'zkteco-adms');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'zkteco-adms');

        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncZktecoAttendanceCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/zkteco-adms.php' => config_path('zkteco-adms.php'),
        ], 'zkteco-adms-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'zkteco-adms-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/zkteco-adms'),
        ], 'zkteco-adms-views');
        
    }

    protected function registerRoutes(): void
    {
        if (! config('zkteco-adms.routes.enabled', true)) {
            return;
        }

        Route::middleware(config('zkteco-adms.routes.middleware', ['web']))
            ->group(__DIR__.'/../routes/web.php');
    }
}
