<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use App\Services\DashboardService;
use App\Services\ThemeService;
use App\Services\AuthService;

class MultiDashboardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService();
        });

        $this->app->singleton(ThemeService::class, function ($app) {
            return new ThemeService();
        });

        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load dynamic dashboard configurations
        $this->loadDynamicConfigurations();
        
        // Register dynamic routes
        $this->registerDynamicRoutes();
        
        // Load dynamic guards
        $this->loadDynamicGuards();
    }

    /**
     * Load dynamic dashboard configurations
     */
    protected function loadDynamicConfigurations(): void
    {
        try {
            // Only load configurations if the dashboard_types table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('dashboard_types')) {
                return;
            }

            $dashboardService = app(DashboardService::class);
            $dashboards = $dashboardService->getActiveDashboards();

            foreach ($dashboards as $dashboard) {
                Config::set("dashboards.{$dashboard->type}", [
                    'name' => $dashboard->name,
                    'description' => $dashboard->description,
                    'theme' => $dashboard->theme_config,
                    'auth_methods' => $dashboard->auth_methods,
                    'settings' => $dashboard->settings,
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail during migrations or when database is not ready
            \Illuminate\Support\Facades\Log::debug('Dashboard configurations not loaded: ' . $e->getMessage());
        }
    }

    /**
     * Register dynamic routes for each dashboard type
     */
    protected function registerDynamicRoutes(): void
    {
        if (file_exists(base_path('routes/dashboard-routes.php'))) {
            require base_path('routes/dashboard-routes.php');
        }
    }

    /**
     * Load dynamic authentication guards
     */
    protected function loadDynamicGuards(): void
    {
        try {
            // Only load guards if the dashboard_types table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('dashboard_types')) {
                return;
            }

            $dashboardService = app(DashboardService::class);
            $dashboards = $dashboardService->getActiveDashboards();

            foreach ($dashboards as $dashboard) {
                Config::set("auth.guards.{$dashboard->type}", [
                    'driver' => 'session',
                    'provider' => $dashboard->type,
                ]);

                Config::set("auth.guards.api-{$dashboard->type}", [
                    'driver' => 'jwt',
                    'provider' => $dashboard->type,
                ]);

                Config::set("auth.providers.{$dashboard->type}", [
                    'driver' => 'eloquent',
                    'model' => "App\\Models\\" . \Illuminate\Support\Str::studly($dashboard->type) . "\\User",
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail during migrations or when database is not ready
            \Illuminate\Support\Facades\Log::debug('Dynamic guards not loaded: ' . $e->getMessage());
        }
    }
}