<?php

use Illuminate\Support\Facades\Route;
use Modules\Cashier\Http\Controllers\AuthController;

Route::prefix('cashier')->group(function () {
    Route::middleware('guest:cashier')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:cashier')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});
