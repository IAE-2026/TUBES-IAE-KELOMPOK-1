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
        $cart = Cart::create($request->only([
            'user_id',
            'product_id',
            'quantity',
            'price',
        ]));

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