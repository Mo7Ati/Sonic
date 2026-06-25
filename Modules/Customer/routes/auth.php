<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\AuthController;

Route::prefix('customer')->group(function () {

    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
