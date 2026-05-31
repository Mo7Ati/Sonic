<?php

use Illuminate\Support\Facades\Route;
use Modules\Cashier\Http\Controllers\BranchController;
use Modules\Cashier\Http\Controllers\OrderController;
use Modules\Cashier\Http\Controllers\PaymentMethodController;
use Modules\Cashier\Http\Controllers\ProductController;

Route::prefix('cashier')->middleware(['auth:cashier'])->group(function () {
    // Orders
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::post('orders/{order}/confirm-payment', [OrderController::class, 'confirmPayment']);

    // Payment Methods
    Route::get('payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('payment-methods', [PaymentMethodController::class, 'store']);
    Route::patch('payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
    Route::delete('payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy']);

    // Branch Products
    Route::get('products', [ProductController::class, 'index']);
    Route::patch('products/{product}', [ProductController::class, 'update']);

    // Branch
    Route::get('branch', [BranchController::class, 'show']);
    Route::patch('branch/status', [BranchController::class, 'updateStatus']);
});
