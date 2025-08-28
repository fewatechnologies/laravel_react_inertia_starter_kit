<?php

use App\Http\Controllers\Api\Test\AuthController;
use Illuminate\Support\Facades\Route;

// Test Dashboard API Routes
Route::prefix('test')->name('api.test.')->group(function () {
    // Authentication Routes
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    
    // SMS OTP Routes
    Route::post('/send-otp', [AuthController::class, 'sendSmsOtp'])->name('send-otp');
    Route::post('/verify-otp', [AuthController::class, 'verifySmsOtp'])->name('verify-otp');
    
    // Protected Routes
    Route::middleware('auth:api-test')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    });
});