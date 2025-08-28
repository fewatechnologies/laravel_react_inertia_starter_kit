<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AakashSmsService
{
    protected $token;
    protected $from;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = config('services.aakash_sms.token');
        $this->from = config('services.aakash_sms.from', 'Laravel');
        $this->baseUrl = 'https://sms.aakashsms.com/sms/v3';
    }

    /**
     * Send OTP SMS
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        $message = "Your OTP code is: {$otp}. Valid for 5 minutes. Do not share this code.";

        return $this->sendSms($phone, $message);
    }

    /**
     * Send SMS
     */
    public function sendSms(string $phone, string $message): bool
    {
        if (!$this->token) {
            Log::warning('Aakash SMS token not configured');
            return false;
        }

        try {
            $response = Http::post($this->baseUrl . '/send', [
                'auth_token' => $this->token,
                'to' => $this->formatPhone($phone),
                'text' => $message,
            ]);

            $result = $response->json();

            if ($response->successful() && !($result['error'] ?? true)) {
                Log::info('SMS sent successfully', [
                    'phone' => $phone,
                    'response' => $result
                ]);
                return true;
            }

            Log::error('SMS sending failed', [
                'phone' => $phone,
                'response' => $result,
                'status' => $response->status()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('SMS service error', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get SMS credit balance
     */
    public function getBalance(): ?int
    {
        if (!$this->token) {
            return null;
        }

        try {
            $response = Http::get($this->baseUrl . '/../v1/credit', [
                'auth_token' => $this->token,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result['balance'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get SMS balance', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Format phone number
     */
    protected function formatPhone(string $phone): string
    {
        // Remove any spaces, dashes, or special characters
        $phone = preg_replace('/[^\d]/', '', $phone);

        // If phone starts with 0, replace with 977 (Nepal country code)
        if (str_starts_with($phone, '0')) {
            $phone = '977' . substr($phone, 1);
        }

        // If phone doesn't start with country code, add 977
        if (!str_starts_with($phone, '977') && strlen($phone) === 10) {
            $phone = '977' . $phone;
        }

        return $phone;
    }

    /**
     * Check if service is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->token);
    }
}
