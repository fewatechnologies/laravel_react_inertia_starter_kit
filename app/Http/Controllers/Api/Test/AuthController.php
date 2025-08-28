<?php

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\Controller;
use App\Models\Test\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('auth:api-test', ['except' => ['login', 'register', 'sendSmsOtp', 'verifySmsOtp']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check rate limiting
        $rateLimit = $this->authService->checkRateLimit($request->email, 'test');
        if (!$rateLimit['allowed']) {
            return response()->json([
                'error' => 'Too many login attempts',
                'reset_in' => $rateLimit['reset_in']
            ], 429);
        }

        if (!$token = Auth::guard('api-test')->attempt($credentials)) {
            $this->authService->incrementAuthAttempts($request->email, 'test');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->authService->clearAuthAttempts($request->email, 'test');
        
        return $this->respondWithToken($token);
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

        $token = Auth::guard('api-test')->login($user);

        return $this->respondWithToken($token);
    }

    public function me()
    {
        $user = Auth::guard('api-test')->user();
        
        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function logout()
    {
        Auth::guard('api-test')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api-test')->refresh());
    }

    public function sendSmsOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $result = $this->authService->sendSmsOtp($request->phone, 'test');

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_in' => $result['expires_in']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    public function verifySmsOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
        ]);

        if ($this->authService->verifySmsOtp($request->phone, $request->otp, 'test')) {
            // Find user with this phone number
            $user = User::where('phone', $request->phone)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with this phone number'
                ], 404);
            }

            // Update phone verification
            $user->update(['phone_verified_at' => now()]);
            
            $token = Auth::guard('api-test')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Phone verified successfully',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::guard('api-test')->factory()->getTTL() * 60,
                'user' => $user
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP'
        ], 400);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api-test')->factory()->getTTL() * 60,
            'user' => Auth::guard('api-test')->user()
        ]);
    }
}