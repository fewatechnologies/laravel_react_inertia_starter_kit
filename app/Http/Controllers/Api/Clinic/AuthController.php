<?php

namespace App\Http\Controllers\Api\Clinic;

use App\Http\Controllers\Controller;
use App\Models\Clinic\User;
use App\Services\AakashSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $query = User::where('email', $request->email);
        
        if ('shared' === 'shared') {
            $query->dashboardType('clinic');
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

        $token = $user->createToken('clinic-token')->plainTextToken;
        $user->updateLastLogin();

        return response()->json([
            'user' => $user,
            'token' => $token,
            'dashboard_type' => 'clinic',
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $query = User::where('phone', $request->phone);
        
        if ('shared' === 'shared') {
            $query->dashboardType('clinic');
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

        $otp = random_int(100000, 999999);
        
        // Store OTP in cache with 5-minute expiry
        cache()->put("otp_{$user->id}", $otp, now()->addMinutes(5));

        try {
            $smsService = app(AakashSmsService::class);
            $smsService->sendOtp($user->phone, $otp);
            
            return response()->json(['message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send OTP'], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        $query = User::where('phone', $request->phone);
        
        if ('shared' === 'shared') {
            $query->dashboardType('clinic');
        }

        $user = $query->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => ['Invalid phone number.'],
            ]);
        }

        $storedOtp = cache()->get("otp_{$user->id}");

        if (!$storedOtp || $storedOtp !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        cache()->forget("otp_{$user->id}");

        $token = $user->createToken('clinic-token')->plainTextToken;
        $user->updateLastLogin();

        return response()->json([
            'user' => $user,
            'token' => $token,
            'dashboard_type' => 'clinic',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'dashboard_type' => 'clinic',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . $user->getTable() . ',email,' . $user->id,
            'phone' => 'nullable|string|unique:' . $user->getTable() . ',phone,' . $user->id,
            'profile_data' => 'nullable|array',
        ]);

        $user->update($validated);

        return response()->json([
            'user' => $user->fresh(),
            'message' => 'Profile updated successfully',
        ]);
    }
}