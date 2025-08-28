<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('shop')->user();
        
        $stats = [
            'total_users' => User::dashboardType('shop')->count(),
            'active_users' => User::dashboardType('shop')->where('is_active', true)->count(),
            'recent_logins' => User::dashboardType('shop')->whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(7))->count(),
        ];

        return Inertia::render('Shop/Dashboard', [
            'user' => $user,
            'stats' => $stats,
            'dashboardType' => 'shop',
        ]);
    }

    public function profile()
    {
        $user = Auth::guard('shop')->user();

        return Inertia::render('Shop/Profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('shop')->user();
        
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