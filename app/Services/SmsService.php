<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $client;
    protected $baseUrl;
    protected $authToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('services.aakash_sms.base_url', 'https://sms.aakashsms.com/sms/');
        $this->authToken = config('services.aakash_sms.token');
    }

    /**
     * Send SMS using Aakash SMS API
     */
    public function sendSms(string $to, string $message): array
    {
        if (!$this->authToken) {
            return [
                'success' => false,
                'message' => 'SMS service not configured'
            ];
        }

        try {
            $response = $this->client->post($this->baseUrl . 'v3/send', [
                'form_params' => [
                    'auth_token' => $this->authToken,
                    'to' => $this->formatPhoneNumber($to),
                    'text' => $message,
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['error']) && $responseData['error'] === false) {
                Log::info('SMS sent successfully', [
                    'to' => $to,
                    'message_length' => strlen($message),
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => $responseData['data'] ?? null
                ];
            }

            Log::warning('SMS sending failed', [
                'to' => $to,
                'response' => $responseData
            ]);

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Failed to send SMS',
                'data' => $responseData
            ];

        } catch (RequestException $e) {
            Log::error('SMS API request failed', [
                'to' => $to,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => 'SMS service temporarily unavailable'
            ];
        } catch (\Exception $e) {
            Log::error('SMS sending error', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS'
            ];
        }
    }

    /**
     * Send SMS to multiple recipients
     */
    public function sendBulkSms(array $recipients, string $message): array
    {
        $to = implode(',', array_map([$this, 'formatPhoneNumber'], $recipients));
        
        return $this->sendSms($to, $message);
    }

    /**
     * Get SMS reports
     */
    public function getReports(int $page = 1): array
    {
        if (!$this->authToken) {
            return [
                'success' => false,
                'message' => 'SMS service not configured'
            ];
        }

        try {
            $response = $this->client->post($this->baseUrl . 'v1/report/api', [
                'form_params' => [
                    'auth_token' => $this->authToken,
                    'page' => $page,
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['error']) && $responseData['error'] === false) {
                return [
                    'success' => true,
                    'data' => $responseData['data'] ?? [],
                    'total_pages' => $responseData['total_page'] ?? 1,
                    'current_page' => $page
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Failed to fetch reports'
            ];

        } catch (RequestException $e) {
            Log::error('SMS reports API request failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => 'SMS service temporarily unavailable'
            ];
        }
    }

    /**
     * Get account balance/credit
     */
    public function getBalance(): array
    {
        if (!$this->authToken) {
            return [
                'success' => false,
                'message' => 'SMS service not configured'
            ];
        }

        try {
            $response = $this->client->post($this->baseUrl . 'v1/credit', [
                'form_params' => [
                    'auth_token' => $this->authToken,
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['available_credit'])) {
                return [
                    'success' => true,
                    'available_credit' => $responseData['available_credit'],
                    'total_sms_sent' => $responseData['total_sms_sent'] ?? 0,
                    'response_code' => $responseData['response_code'] ?? null
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch balance information'
            ];

        } catch (RequestException $e) {
            Log::error('SMS balance API request failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => 'SMS service temporarily unavailable'
            ];
        }
    }

    /**
     * Format phone number for API
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        // If it starts with +977, remove country code
        if (strpos($cleanPhone, '977') === 0) {
            $cleanPhone = substr($cleanPhone, 3);
        }
        
        // Ensure it's a valid Nepali mobile number format
        if (strlen($cleanPhone) === 10 && strpos($cleanPhone, '98') === 0) {
            return $cleanPhone;
        }
        
        // If it's 9 digits starting with 8, prepend 9
        if (strlen($cleanPhone) === 9 && strpos($cleanPhone, '8') === 0) {
            return '9' . $cleanPhone;
        }
        
        return $cleanPhone;
    }

    /**
     * Validate phone number format
     */
    public function validatePhoneNumber(string $phone): bool
    {
        $cleanPhone = $this->formatPhoneNumber($phone);
        
        // Check if it's a valid Nepali mobile number (10 digits starting with 98)
        return preg_match('/^98\d{8}$/', $cleanPhone);
    }

    /**
     * Get SMS cost estimation
     */
    public function estimateCost(string $message, int $recipients = 1): array
    {
        $messageLength = strlen($message);
        $smsCount = ceil($messageLength / 160); // Standard SMS length is 160 characters
        
        return [
            'message_length' => $messageLength,
            'sms_parts' => $smsCount,
            'recipients' => $recipients,
            'total_sms' => $smsCount * $recipients,
            'estimated_cost' => $smsCount * $recipients // 1 credit per SMS part
        ];
    }
}