<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\AddressController;
use Modules\Customer\Http\Controllers\BranchesController;
use Modules\Customer\Http\Controllers\CartController;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\Customer\Http\Controllers\HomeController;
use Modules\Customer\Http\Controllers\ProductsController;
use Modules\Customer\Http\Controllers\StoreCategoriesController;

Route::prefix('customer')->group(function () {
    Route::get('splash', [CustomerController::class, 'index']);
    Route::get('home', [HomeController::class, 'index']);

    // Store Categories
    Route::get('store-categories/{category_id}', [StoreCategoriesController::class, 'show']);

    // Branches
    Route::get('branches', [BranchesController::class, 'index']);
    Route::get('branches/{id}', [BranchesController::class, 'show']);

    // Products
    Route::get('products/{id}', [ProductsController::class, 'show']);

    // Cart (accessible to both guests via X-Session-Id and authenticated users)
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/items', [CartController::class, 'addItem']);
        Route::put('/items/{id}', [CartController::class, 'updateItem']);
        Route::delete('/items/{id}', [CartController::class, 'removeItem']);
        Route::delete('/', [CartController::class, 'clear']);
    });

    // Addresses (accessible to both guests via X-Session-Id and authenticated users)
    Route::prefix('addresses')->group(function () {
        Route::get('/fields', [AddressController::class, 'fields']);
        Route::get('/', [AddressController::class, 'index']);
        Route::get('/{id}', [AddressController::class, 'show']);
        Route::post('/', [AddressController::class, 'store']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
    });
});
