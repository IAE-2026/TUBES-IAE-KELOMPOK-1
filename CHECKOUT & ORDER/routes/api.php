<?php

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\GraphQLController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::post('/graphql', GraphQLController::class)->middleware('iae.key');

Route::middleware(['iae.key', 'sso.role'])->group(function (): void {
    // Checkout routes (singular per kontrak dosen)
    Route::get('/checkout', [CheckoutController::class, 'index']);
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::get('/checkout/{checkout}', [CheckoutController::class, 'show']);

    // Payment routes (singular per kontrak dosen)
    Route::get('/payment/methods', [PaymentController::class, 'methods']);
    Route::post('/payment', [PaymentController::class, 'store']);
    Route::get('/payment/{payment}', [PaymentController::class, 'show']);
    Route::get('/payment/{payment}/status', [PaymentController::class, 'status']);
    Route::post('/payment/confirm', [PaymentController::class, 'confirm']);

    // Order routes (plural per kontrak dosen)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store'])->middleware('sso.role:customer,system,admin');
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
});

Route::fallback(fn () => ApiResponse::error('Resource not found', null, 404));
