<?php

use App\Http\Controllers\MasterAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to master admin dashboard
Route::get('/', function () {
    return redirect()->route('master.admin.dashboard');
});

// Master Admin Routes
Route::prefix('master-admin')->name('master.admin.')->group(function () {
    Route::get('/', [MasterAdminController::class, 'index'])->name('dashboard');
    Route::get('/create', [MasterAdminController::class, 'create'])->name('create');
    Route::post('/create', [MasterAdminController::class, 'store'])->name('store');
    Route::get('/edit/{dashboard}', [MasterAdminController::class, 'edit'])->name('edit');
    Route::put('/edit/{dashboard}', [MasterAdminController::class, 'update'])->name('update');
    Route::delete('/delete/{dashboard}', [MasterAdminController::class, 'destroy'])->name('destroy');
    Route::patch('/toggle-status/{dashboard}', [MasterAdminController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/apply-theme/{dashboard}', [MasterAdminController::class, 'applyThemePreset'])->name('apply-theme');
    Route::get('/stats', [MasterAdminController::class, 'getSystemStats'])->name('stats');
    Route::post('/command', [MasterAdminController::class, 'runCommand'])->name('command');
});

// Dynamic dashboard routes will be loaded here
// This file will be updated automatically when dashboards are created
require __DIR__ . '/test.php';