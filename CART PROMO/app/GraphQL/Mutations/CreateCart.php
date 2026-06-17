<?php

namespace App\GraphQL\Mutations;

use App\Models\Cart;

final class CreateCart
{
    public function __invoke($_, array $args): array
    {
        $cart = Cart::create([
            'user_id' => $args['user_id'],
            'product_id' => $args['product_id'],
            'quantity' => $args['quantity'],
            'price' => $args['price'],
        ]);

        return [
            'status' => 'success',
            'message' => 'Cart created successfully',
            'data' => $cart,
        ];
    }
}