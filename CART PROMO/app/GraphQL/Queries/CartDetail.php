<?php

namespace App\GraphQL\Queries;

use App\Models\Cart;

final class CartDetail
{
    public function __invoke($_, array $args): array
    {
        $cart = Cart::find($args['id']);

        if (!$cart) {
            return [
                'status' => 'error',
                'message' => 'Cart tidak ditemukan',
                'data' => null,
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Cart detail',
            'data' => $cart,
        ];
    }
}