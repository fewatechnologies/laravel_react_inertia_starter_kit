<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Services\AakashSmsService;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Hospital/Auth/Login', [
            'dashboardType' => 'hospital',
            'authMethods' => ['email', 'sms'],
            'hasLandingPage' => 'true',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $query = User::where('email', $request->email);
        
        // Add dashboard type scope for shared database
        if ('separate' === 'shared') {
            $query->dashboardType('hospital');
        }

        $user = $query->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is deactivated.'],
            ]);
        }

        Auth::guard('hospital')->login($user, $request->boolean('remember'));
        $user->updateLastLogin();

        $request->session()->regenerate();

        return redirect()->intended(route('hospital.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('hospital')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('hospital.login');
    }

        
    /**
     * Send SMS OTP
     */
    public function sendSmsOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $query = User::where('phone', $request->phone);
        
        if ('separate' === 'shared') {
            $query->dashboardType('hospital');
        }

        $user = $query->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => ['No account found with this phone number.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'phone' => ['Your account is deactivated.'],
            ]);
        }

        // Generate OTP
        $otp = random_int(100000, 999999);
        
        // Store OTP in session (in production, use cache with expiry)
        session(['sms_otp_' . $user->id => $otp, 'sms_otp_user_id' => $user->id]);

        // Send SMS
        try {
            $smsService = app(AakashSmsService::class);
            $smsService->sendOtp($user->phone, $otp);
            
            return response()->json(['message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send OTP'], 500);
        }
    }

    /**
     * Verify SMS OTP and login
     */
    public function verifySmsOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        $userId = session('sms_otp_user_id');
        $storedOtp = session('sms_otp_' . $userId);

        if (!$userId || !$storedOtp || $storedOtp !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP code.'],
            ]);
        }

        $user = User::find($userId);

        if (!$user || $user->phone !== $request->phone) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP code.'],
            ]);
        }

        // Clear OTP from session
        session()->forget(['sms_otp_' . $userId, 'sms_otp_user_id']);

        // Login user
        Auth::guard('hospital')->login($user, true);
        $user->updateLastLogin();

        $request->session()->regenerate();

        return redirect()->intended(route('hospital.dashboard'));
    }
}