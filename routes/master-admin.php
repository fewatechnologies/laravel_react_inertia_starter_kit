<?php

use App\Http\Controllers\MasterAdmin\AuthController;
use App\Http\Controllers\MasterAdmin\DashboardController;
use Illuminate\Support\Facades\Route;

// Master Admin Authentication Routes
Route::prefix('master-admin')->name('master-admin.')->group(function () {
    Route::middleware('guest:master-admin')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:master-admin')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::patch('dashboards/{id}/toggle', [DashboardController::class, 'toggleDashboard'])->name('dashboards.toggle');
    });
});
