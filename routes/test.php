<?php

use App\Http\Controllers\Test\AuthController;
use App\Http\Controllers\Test\DashboardController;
use Illuminate\Support\Facades\Route;

// Test Dashboard Routes
Route::prefix('test')->name('test.')->group(function () {
    // Authentication Routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // SMS OTP Routes
    Route::post('/send-otp', [AuthController::class, 'sendSmsOtp'])->name('send-otp');
    Route::post('/verify-otp', [AuthController::class, 'verifySmsOtp'])->name('verify-otp');
    
    // Protected Routes
    Route::middleware('auth:test')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    });
});