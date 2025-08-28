<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use App\Models\DashboardType;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboards = DashboardType::with(['activity'])->get()->map(function ($dashboard) {
            return [
                'id' => $dashboard->id,
                'type' => $dashboard->type,
                'name' => $dashboard->name,
                'description' => $dashboard->description,
                'database_strategy' => $dashboard->database_strategy,
                'users_count' => $dashboard->getUsersCount(),
                'is_active' => $dashboard->is_active,
                'created_at' => $dashboard->created_at,
                'has_landing_page' => $dashboard->has_landing_page,
            ];
        });

        $stats = [
            'total_dashboards' => DashboardType::count(),
            'active_dashboards' => DashboardType::active()->count(),
            'shared_db_dashboards' => DashboardType::byStrategy('shared')->count(),
            'separate_db_dashboards' => DashboardType::byStrategy('separate')->count(),
            'total_users' => $dashboards->sum('users_count'),
        ];

        return Inertia::render('MasterAdmin/Dashboard', [
            'dashboards' => $dashboards,
            'stats' => $stats,
        ]);
    }

    public function toggleDashboard(Request $request, $id)
    {
        $dashboard = DashboardType::findOrFail($id);
        
        $dashboard->update([
            'is_active' => !$dashboard->is_active
        ]);

        return back()->with('success', "Dashboard {$dashboard->name} has been " . ($dashboard->is_active ? 'activated' : 'deactivated') . '.');
    }
}