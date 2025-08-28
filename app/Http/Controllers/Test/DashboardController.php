<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\Test\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:test');
    }

    public function index(): Response
    {
        $user = auth('test')->user();
        
        return Inertia::render('Test/Dashboard', [
            'dashboardType' => 'test',
            'user' => $user,
            'stats' => $this->getDashboardStats(),
            'userRoles' => $user->getRoleNames(),
            'userPermissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    protected function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'recent_logins' => User::where('updated_at', '>=', now()->subDays(7))->count(),
        ];
    }

    public function profile(): Response
    {
        return Inertia::render('Test/Profile', [
            'user' => auth('test')->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth('test')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:test_users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:test_users,phone,' . $user->id,
            'theme_preferences' => 'nullable|array',
            'language' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
        ]);

        $user->update($request->only([
            'name', 'email', 'phone', 'theme_preferences', 'language', 'timezone'
        ]));

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}