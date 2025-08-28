<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Models\Clinic\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('clinic')->user();
        
        $stats = [
            'total_users' => User::dashboardType('clinic')->count(),
            'active_users' => User::dashboardType('clinic')->where('is_active', true)->count(),
            'recent_logins' => User::dashboardType('clinic')->whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(7))->count(),
        ];

        return Inertia::render('Clinic/Dashboard', [
            'user' => $user,
            'stats' => $stats,
            'dashboardType' => 'clinic',
        ]);
    }

    public function profile()
    {
        $user = Auth::guard('clinic')->user();

        return Inertia::render('Clinic/Profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('clinic')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . $user->getTable() . ',email,' . $user->id,
            'phone' => 'nullable|string|unique:' . $user->getTable() . ',phone,' . $user->id,
            'profile_data' => 'nullable|array',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}