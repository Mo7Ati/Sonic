<?php

use Illuminate\Support\Facades\Route;
use Modules\Cashier\Http\Controllers\BranchController;
use Modules\Cashier\Http\Controllers\OrderController;
use Modules\Cashier\Http\Controllers\ProductController;

Route::prefix('cashier')->middleware(['auth:cashier'])->group(function () {
    // Orders
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);

    // Branch Products
    Route::get('products', [ProductController::class, 'index']);
    Route::patch('products/{product}', [ProductController::class, 'update']);

    // Branch
    Route::get('branch', [BranchController::class, 'show']);
    Route::patch('branch/status', [BranchController::class, 'updateStatus']);
});
