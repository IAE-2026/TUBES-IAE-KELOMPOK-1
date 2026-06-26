<?php
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Product Service API",
 *      description="Swagger Documentation"
 * )
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

Route::middleware('apikey')->prefix('v1')->group(function () {

    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/{id}/stock', [ProductController::class, 'stock']);

    Route::put('/products/{id}/update', [ProductController::class, 'updateStock']);

});

Route::middleware('sso')->prefix('v1')->group(function () {
    Route::get('/sso/me', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'status' => 'success',
            'message' => 'SSO authentication successful',
            'data' => [
                'user' => auth()->user(),
                'roles' => auth()->user()->roles,
                'token_payload' => $request->attributes->get('sso_payload')
            ],
            'meta' => [
                'service_name' => 'Product-Service',
                'api_version' => 'v1'
            ]
        ]);
    });

    Route::put('/sso/products/{id}/update', [ProductController::class, 'updateStock']);
});