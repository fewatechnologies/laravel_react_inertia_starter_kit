<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DashboardGenerator;
use App\Services\DatabaseManager;
use App\Services\AakashSmsService;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DashboardGenerator::class);
        $this->app->singleton(DatabaseManager::class);
        $this->app->singleton(AakashSmsService::class);
    }

    public function boot(): void
    {
        // Load dynamic dashboard configurations
        $this->loadDynamicDashboardConfigs();
    }

    protected function loadDynamicDashboardConfigs(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('dashboard_types')) {
                return;
            }

            $dashboards = \App\Models\DashboardType::active()->get();

            foreach ($dashboards as $dashboard) {
                // Register auth guards dynamically
                $this->registerAuthGuard($dashboard);

                // Load database connections
                $this->loadDatabaseConnection($dashboard);
            }
        } catch (\Exception $e) {
            // Silent fail during migrations
        }
    }

    protected function registerAuthGuard($dashboard): void
    {
        config([
            "auth.guards.{$dashboard->type}" => [
                'driver' => 'session',
                'provider' => "{$dashboard->type}_users",
            ],
            "auth.providers.{$dashboard->type}_users" => [
                'driver' => 'eloquent',
                'model' => "App\\Models\\{$dashboard->getModelNamespace()}\\User",
            ]
        ]);
    }

    protected function loadDatabaseConnection($dashboard): void
    {
        if ($dashboard->database_strategy === 'separate') {
            $configFile = config_path("database_connections/{$dashboard->type}.php");

            if (file_exists($configFile)) {
                $dbConfig = require $configFile;
                config(["database.connections.{$dbConfig['connection_name']}" => $dbConfig['config']]);
            }
        }
    }
}
