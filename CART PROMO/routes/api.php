<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\AuthController;

// Auth routes (public)
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});



// Protected auth routes
Route::prefix('v1/auth')->middleware('jwt.auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

// Protected cart & promo routes
Route::prefix('v1')->middleware('jwt.auth')->group(function () {

    Route::get('/products', [CartController::class, 'products']);

    // Cart routes (sesuai kontrak dosen)
    Route::get('/carts', [CartController::class, 'index']);
    Route::post('/carts', [CartController::class, 'store']);
    Route::delete('/carts/{id}', [CartController::class, 'destroy']);
    Route::get('/carts/{id}', [CartController::class, 'show']);

    // Promo routes (jamak: promos, sesuai kontrak dosen)
    Route::get('/promos', [PromoController::class, 'index']);
    Route::get('/promos/{id}', [PromoController::class, 'show']);
    Route::post('/promos/apply', [PromoController::class, 'apply']);
});