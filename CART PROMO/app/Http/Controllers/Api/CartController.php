<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Services\RabbitMqPublisher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

class CartController extends Controller
{
    #[OA\Get(
        path: '/api/v1/products',
        operationId: 'getProducts',
        tags: ['Products'],
        summary: 'Get list products',
        responses: [
            new OA\Response(response: 200, description: 'Products retrieved successfully'),
        ]
    )]
    public function products()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'data' => []
        ]);
    }

    /**
     * GET /api/v1/carts - Tampilkan semua isi keranjang
     */
    public function index()
    {
        $carts = Cart::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Carts retrieved successfully',
            'data' => $carts
        ]);
    }

    #[OA\Post(
        path: '/api/v1/carts',
        operationId: 'createCart',
        tags: ['Carts'],
        summary: 'Create cart item',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'product_id', 'quantity', 'price'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'integer', example: 1),
                    new OA\Property(property: 'product_id', type: 'integer', example: 101),
                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 50000),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Cart created successfully'),
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        // Get user_id from token if not provided in the request body
        $userId = $request->input('user_id');
        if (!$userId) {
            $ssoUser = $request->attributes->get('sso_user');
            if ($ssoUser) {
                $email = $ssoUser->sub ?? $ssoUser->email ?? '';
                preg_match('/\d+/', $email, $matches);
                $userId = !empty($matches) ? (int)$matches[0] : 1;
            } else {
                $userId = 1;
            }
        }

        // Fetch price from Service A (Produk & Stok) if not provided in the request body
        $price = $request->input('price');
        if (!$price) {
            try {
                $productId = $request->input('product_id');
                // Service A requires X-IAE-KEY header (using KEY-MHS-282)
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'X-IAE-KEY' => 'KEY-MHS-282'
                ])->timeout(5)
                  ->get("http://produk-stok-app:8000/api/v1/products/{$productId}");

                if ($response->successful()) {
                    $price = $response->json('data.price');
                }
            } catch (\Exception $e) {
                \Log::error('Failed to fetch product price from Service A: ' . $e->getMessage());
            }

            // Fallback price if API request fails
            if (!$price) {
                $price = 500000;
            }
        }

        $cart = Cart::create([
            'user_id' => $userId,
            'product_id' => $request->input('product_id'),
            'quantity' => $request->input('quantity'),
            'price' => $price,
        ]);

        // Publish cart.created event to RabbitMQ
        try {
            $publisher = new RabbitMqPublisher();
            $publisher->publish('cart.created', $cart->toArray());
        } catch (\Exception $e) {
            \Log::error('RabbitMQ publish failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cart created successfully',
            'data' => $cart
        ]);
    }

    #[OA\Get(
        path: '/api/v1/carts/{id}',
        operationId: 'getCartDetail',
        tags: ['Carts'],
        summary: 'Get cart detail',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cart detail'),
            new OA\Response(response: 404, description: 'Cart not found'),
        ]
    )]
    public function show($id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart tidak ditemukan',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cart detail',
            'data' => $cart
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/carts/{id}',
        operationId: 'deleteCart',
        tags: ['Carts'],
        summary: 'Delete cart item',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cart deleted successfully'),
            new OA\Response(response: 404, description: 'Cart not found'),
        ]
    )]
    public function destroy($id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $cart->delete();

        // Publish cart.deleted event to RabbitMQ
        try {
            $publisher = new RabbitMqPublisher();
            $publisher->publish('cart.deleted', ['cart_id' => $id]);
        } catch (\Exception $e) {
            \Log::error('RabbitMQ publish failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cart deleted successfully',
            'data' => null
        ]);
    }
}