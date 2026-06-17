<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('api.key')->group(function () {
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/product/{product_id}', [ReviewController::class, 'byProduct']);
    Route::post('/reviews', [ReviewController::class, 'store']);
});