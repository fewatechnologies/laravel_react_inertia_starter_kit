<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\doctor\AuthController;
use App\Http\Controllers\doctor\DashboardController;

// Authentication Routes
Route::prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // Protected Routes
    Route::middleware('auth:doctor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});