<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Dynamic dashboard API routes
require __DIR__ . '/api/test.php';
require __DIR__.'/api/clinic.php';
