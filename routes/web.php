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




// Dynamic dashboard routes will be loaded here
// This file will be updated automatically when dashboards are created
require __DIR__.'/master-admin.php';
require __DIR__.'/clinic.php';
