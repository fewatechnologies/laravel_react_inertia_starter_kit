<?php

use App\Http\Controllers\Api\Clinic\AuthController;
use Illuminate\Http\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

Route::prefix('api/clinic')->name('api.clinic.')->group(function () {
    // Public API routes
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('send-otp', [AuthController::class, 'sendOtp'])->name('send-otp');
        Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
    });

    // Protected API routes
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('user', [AuthController::class, 'user'])->name('user');
        Route::put('user/profile', [AuthController::class, 'updateProfile'])->name('user.profile');
    });
});