<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\HomeController;

Route::prefix('customer')->group(function () {
    Route::middleware(['auth:sanctum'])->prefix('customer')->group(function () {

    });

    Route::get('home', [HomeController::class, 'index']);

});
