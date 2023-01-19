<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kayo\StatesAndCitiesIbge\Services\Integration\IbgeRestIntegrationService;

class StatesAndCitiesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfig();
        $this->registerCommands();
    }

    public function register()
    {
        $this->app->singleton(IbgeRestIntegrationService::class, function ($app) {
            return new IbgeRestIntegrationService(
                config('ibge.integration.host', 'http://localhost:8000'),
                config('ibge.integration.timeout', 20)
            );
        });
    }

    private function registerCommands()
    {
    }

    private function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/ibge.php' => config_path('ibge.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }
}
