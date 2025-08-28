<?php

use App\Http\Controllers\Clinic\AuthController;
use App\Http\Controllers\Clinic\DashboardController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::prefix('clinic')->name('clinic.')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    
    // Authentication Routes
    Route::middleware('guest:clinic')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
        Route::post('send-sms-otp', [AuthController::class, 'sendSmsOtp'])->name('send-sms-otp');
        Route::post('verify-sms-otp', [AuthController::class, 'verifySmsOtp'])->name('verify-sms-otp');
    });

    // Authenticated Routes
    Route::middleware('auth:clinic')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    });
});