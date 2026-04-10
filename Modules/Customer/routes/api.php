<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\BranchesController;
use Modules\Customer\Http\Controllers\HomeController;
use Modules\Customer\Http\Controllers\StoreCategoriesController;

Route::prefix('customer')->group(function () {
    Route::middleware(['auth:sanctum'])->prefix('customer')->group(function () {});

    Route::get('home', [HomeController::class, 'index']);

    // Store Categories
    Route::get('store-categories/{category_id}', [StoreCategoriesController::class, 'show']);

    // Branches
    Route::get('branches', [BranchesController::class, 'index']);
});
