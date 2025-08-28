<?php

namespace App\Services;

use App\Models\DashboardType;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthService
{
    protected $smsService;

    public function __construct(SmsService $smsService = null)
    {
        $this->smsService = $smsService ?? new SmsService();
    }

    /**
     * Send OTP via SMS
     */
    public function sendSmsOtp(string $phone, string $dashboardType): array
    {
        $dashboard = DashboardType::where('type', $dashboardType)->first();
        
        if (!$dashboard || !$dashboard->hasSmsAuth()) {
            return [
                'success' => false,
                'message' => 'SMS authentication not enabled for this dashboard type'
            ];
        }

        $otp = $this->generateOtp();
        $cacheKey = "sms_otp_{$dashboardType}_{$phone}";
        
        // Store OTP in cache for 10 minutes
        Cache::put($cacheKey, $otp, 600);
        
        $message = "Your {$dashboard->name} verification code is: {$otp}. Valid for 10 minutes.";
        
        $result = $this->smsService->sendSms($phone, $message);
        
        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_in' => 600
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Failed to send SMS'
        ];
    }

    /**
     * Verify SMS OTP
     */
    public function verifySmsOtp(string $phone, string $otp, string $dashboardType): bool
    {
        $cacheKey = "sms_otp_{$dashboardType}_{$phone}";
        $storedOtp = Cache::get($cacheKey);
        
        if (!$storedOtp || $storedOtp !== $otp) {
            return false;
        }
        
        // Remove OTP from cache after successful verification
        Cache::forget($cacheKey);
        
        return true;
    }

    /**
     * Send email verification
     */
    public function sendEmailVerification(string $email, string $dashboardType): array
    {
        $dashboard = DashboardType::where('type', $dashboardType)->first();
        
        if (!$dashboard || !$dashboard->hasEmailAuth()) {
            return [
                'success' => false,
                'message' => 'Email authentication not enabled for this dashboard type'
            ];
        }

        $token = $this->generateVerificationToken();
        $cacheKey = "email_verification_{$dashboardType}_{$email}";
        
        // Store token in cache for 30 minutes
        Cache::put($cacheKey, $token, 1800);
        
        try {
            Mail::send('emails.verification', [
                'token' => $token,
                'dashboard_name' => $dashboard->name,
                'dashboard_type' => $dashboardType,
                'email' => $email,
            ], function ($message) use ($email, $dashboard) {
                $message->to($email)
                        ->subject("Verify your {$dashboard->name} account");
            });
            
            return [
                'success' => true,
                'message' => 'Verification email sent successfully',
                'expires_in' => 1800
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send verification email'
            ];
        }
    }

    /**
     * Verify email token
     */
    public function verifyEmailToken(string $email, string $token, string $dashboardType): bool
    {
        $cacheKey = "email_verification_{$dashboardType}_{$email}";
        $storedToken = Cache::get($cacheKey);
        
        if (!$storedToken || $storedToken !== $token) {
            return false;
        }
        
        // Remove token from cache after successful verification
        Cache::forget($cacheKey);
        
        return true;
    }

    /**
     * Generate 6-digit OTP
     */
    protected function generateOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate verification token
     */
    protected function generateVerificationToken(): string
    {
        return Str::random(32);
    }

    /**
     * Check authentication method availability for dashboard type
     */
    public function getAvailableAuthMethods(string $dashboardType): array
    {
        $dashboard = DashboardType::where('type', $dashboardType)->first();
        
        if (!$dashboard) {
            return ['email']; // Default to email only
        }
        
        return $dashboard->auth_methods ?? ['email'];
    }

    /**
     * Validate phone number format
     */
    public function validatePhoneNumber(string $phone): bool
    {
        // Remove all non-digit characters
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        // Check if it's a valid Nepali phone number (10 digits starting with 98)
        if (preg_match('/^98\d{8}$/', $cleanPhone)) {
            return true;
        }
        
        // Check if it's a valid international format (10-15 digits)
        if (preg_match('/^\d{10,15}$/', $cleanPhone)) {
            return true;
        }
        
        return false;
    }

    /**
     * Format phone number for SMS sending
     */
    public function formatPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        // If it's a Nepali number starting with 98, keep as is
        if (preg_match('/^98\d{8}$/', $cleanPhone)) {
            return $cleanPhone;
        }
        
        // If it's a 10-digit number starting with 98, keep as is
        if (preg_match('/^98\d{8}$/', $cleanPhone)) {
            return $cleanPhone;
        }
        
        // Otherwise return as is (for international numbers)
        return $cleanPhone;
    }

    /**
     * Rate limiting for authentication attempts
     */
    public function checkRateLimit(string $identifier, string $dashboardType, int $maxAttempts = 5): array
    {
        $cacheKey = "auth_attempts_{$dashboardType}_{$identifier}";
        $attempts = Cache::get($cacheKey, 0);
        
        if ($attempts >= $maxAttempts) {
            $remainingTime = Cache::getStore()->getRedis()->ttl($cacheKey);
            
            return [
                'allowed' => false,
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts,
                'reset_in' => $remainingTime > 0 ? $remainingTime : 0
            ];
        }
        
        return [
            'allowed' => true,
            'attempts' => $attempts,
            'max_attempts' => $maxAttempts,
            'remaining' => $maxAttempts - $attempts
        ];
    }

    /**
     * Increment authentication attempts
     */
    public function incrementAuthAttempts(string $identifier, string $dashboardType): void
    {
        $cacheKey = "auth_attempts_{$dashboardType}_{$identifier}";
        $attempts = Cache::get($cacheKey, 0);
        
        // Increment attempts and set expiry to 15 minutes
        Cache::put($cacheKey, $attempts + 1, 900);
    }

    /**
     * Clear authentication attempts
     */
    public function clearAuthAttempts(string $identifier, string $dashboardType): void
    {
        $cacheKey = "auth_attempts_{$dashboardType}_{$identifier}";
        Cache::forget($cacheKey);
    }
}