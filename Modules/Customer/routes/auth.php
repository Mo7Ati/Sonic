<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\AuthController;

Route::prefix('customer')->group(function () {

    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::post('register', [AuthController::class, 'register'])
            ->middleware('throttle:3,1');
        Route::post('register/verify-otp', [AuthController::class, 'verifyRegistrationOtp'])
            ->middleware('throttle:10,1');
        Route::post('register/resend-otp', [AuthController::class, 'resendRegistrationOtp'])
            ->middleware('throttle:3,1');
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::post('email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
            ->middleware('throttle:6,1');
    });

    // Email verification (signed URL)
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});
