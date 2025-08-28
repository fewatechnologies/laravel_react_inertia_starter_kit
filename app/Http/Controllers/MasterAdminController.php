<?php

namespace App\Http\Controllers;

use App\Models\DashboardType;
use App\Services\DashboardService;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class MasterAdminController extends Controller
{
    protected $dashboardService;
    protected $themeService;

    public function __construct(DashboardService $dashboardService, ThemeService $themeService)
    {
        $this->dashboardService = $dashboardService;
        $this->themeService = $themeService;
    }

    /**
     * Show the master admin dashboard
     */
    public function index(): Response
    {
        $dashboards = DashboardType::all();

        // Calculate total users across all dashboard types
        $totalUsers = 0;
        foreach ($dashboards as $dashboard) {
            $totalUsers += $dashboard->users_count;
        }

        $stats = [
            'total_dashboards' => $dashboards->count(),
            'active_dashboards' => $dashboards->where('is_active', true)->count(),
            'total_users' => $totalUsers,
            'recent_activities' => [], // Will be implemented with activity log
        ];

        return Inertia::render('MasterAdmin/Dashboard', [
            'dashboards' => $dashboards,
            'stats' => $stats,
            'themePresets' => $this->themeService->getThemePresets(),
        ]);
    }

    /**
     * Show dashboard creation form
     */
    public function create(): Response
    {
        return Inertia::render('MasterAdmin/CreateDashboard', [
            'themePresets' => $this->themeService->getThemePresets(),
            'authMethods' => ['email', 'sms', 'both'],
            'defaultRoles' => config('dashboards.generation.default_roles'),
            'defaultPermissions' => config('dashboards.generation.default_permissions'),
        ]);
    }

    /**
     * Create a new dashboard type
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|regex:/^[a-z][a-z0-9_]*$/|unique:dashboard_types,type',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'auth_methods' => 'required|array|min:1',
            'auth_methods.*' => 'in:email,sms',
            'theme_preset' => 'required|string',
            'roles' => 'nullable|array',
            'permissions' => 'nullable|array',
        ], [
            'type.regex' => 'Dashboard type must start with a letter and contain only lowercase letters, numbers, and underscores.',
            'type.unique' => 'A dashboard with this type already exists.',
        ]);

        try {
            $dashboardType = $this->dashboardService->createDashboardType([
                'type' => $request->type,
                'name' => $request->name,
                'description' => $request->description,
                'auth_methods' => $request->auth_methods,
                'theme_preset' => $request->theme_preset,
                'roles' => $request->roles ?? [],
                'permissions' => $request->permissions ?? [],
            ]);

            return redirect()->route('master.admin.dashboard')
                ->with('success', "Dashboard type '{$dashboardType->name}' created successfully!");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create dashboard: ' . $e->getMessage()]);
        }
    }

    /**
     * Show dashboard edit form
     */
    public function edit(DashboardType $dashboard): Response
    {
        return Inertia::render('MasterAdmin/EditDashboard', [
            'dashboard' => $dashboard,
            'themePresets' => $this->themeService->getThemePresets(),
            'authMethods' => ['email', 'sms', 'both'],
        ]);
    }

    /**
     * Update dashboard type
     */
    public function update(Request $request, DashboardType $dashboard)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'auth_methods' => 'required|array|min:1',
            'auth_methods.*' => 'in:email,sms',
            'theme_config' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        try {
            $dashboard->update([
                'name' => $request->name,
                'description' => $request->description,
                'auth_methods' => $request->auth_methods,
                'theme_config' => $request->theme_config ?? $dashboard->theme_config,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()->route('master.admin.dashboard')
                ->with('success', "Dashboard '{$dashboard->name}' updated successfully!");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update dashboard: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete dashboard type
     */
    public function destroy(DashboardType $dashboard)
    {
        try {
            $name = $dashboard->name;
            $dashboard->delete();

            return redirect()->route('master.admin.dashboard')
                ->with('success', "Dashboard '{$name}' deleted successfully!");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete dashboard: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle dashboard status
     */
    public function toggleStatus(DashboardType $dashboard)
    {
        try {
            $dashboard->update(['is_active' => !$dashboard->is_active]);

            $status = $dashboard->is_active ? 'activated' : 'deactivated';
            return redirect()->route('master.admin.dashboard')
                ->with('success', "Dashboard '{$dashboard->name}' {$status} successfully!");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update dashboard status: ' . $e->getMessage()]);
        }
    }

    /**
     * Apply theme preset to dashboard
     */
    public function applyThemePreset(Request $request, DashboardType $dashboard)
    {
        $request->validate([
            'preset' => 'required|string',
        ]);

        try {
            $success = $this->themeService->applyThemePreset($dashboard->type, $request->preset);

            if ($success) {
                return redirect()->back()
                    ->with('success', 'Theme preset applied successfully!');
            }

            return back()->withErrors(['error' => 'Invalid theme preset selected.']);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to apply theme preset: ' . $e->getMessage()]);
        }
    }

    /**
     * Get system statistics
     */
    public function getSystemStats()
    {
        $dashboards = DashboardType::all();
        
        // Calculate total users across all dashboard types
        $totalUsers = 0;
        foreach ($dashboards as $dashboard) {
            $totalUsers += $dashboard->users_count;
        }
        
        $stats = [
            'total_dashboards' => $dashboards->count(),
            'active_dashboards' => $dashboards->where('is_active', true)->count(),
            'inactive_dashboards' => $dashboards->where('is_active', false)->count(),
            'total_users' => $totalUsers,
            'sms_enabled_dashboards' => $dashboards->filter(function ($dashboard) {
                return in_array('sms', $dashboard->auth_methods);
            })->count(),
            'email_enabled_dashboards' => $dashboards->filter(function ($dashboard) {
                return in_array('email', $dashboard->auth_methods);
            })->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Run artisan command for dashboard creation
     */
    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
            'arguments' => 'nullable|array',
        ]);

        try {
            $exitCode = Artisan::call($request->command, $request->arguments ?? []);
            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'output' => $output,
                'exit_code' => $exitCode,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}