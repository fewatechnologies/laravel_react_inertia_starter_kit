<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\Test\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:test')->except('logout');
    }

    public function showLogin(): Response
    {
        return Inertia::render('Test/Auth/Login', [
            'dashboardType' => 'test',
            'authMethods' => ['email', 'sms'],
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('test')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('test.dashboard'));
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('test')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('test.login');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Test/Auth/Register', [
            'dashboardType' => 'test',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:test_users',
            'phone' => 'nullable|string|max:20|unique:test_users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Assign default role
        $user->assignRole('user');

        Auth::guard('test')->login($user);

        return redirect()->route('test.dashboard');
    }

    public function sendSmsOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        // For demo purposes, always return success
        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully (Demo: use 123456)',
            'expires_in' => 300
        ]);
    }

    public function verifySmsOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
        ]);

        // For demo purposes, accept 123456 as valid OTP
        if ($request->otp === '123456') {
            // Find or create user with this phone number
            $user = User::where('phone', $request->phone)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with this phone number'
                ], 404);
            }

            // Update phone verification
            $user->update(['phone_verified_at' => now()]);
            
            Auth::guard('test')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Phone verified successfully',
                'redirect' => route('test.dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP'
        ], 400);
    }
}