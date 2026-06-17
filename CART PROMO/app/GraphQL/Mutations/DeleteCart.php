<?php

namespace App\GraphQL\Mutations;

use App\Models\Cart;

final class DeleteCart
{
    public function __invoke($_, array $args): array
    {
        $cart = Cart::find($args['id']);

        if (!$cart) {
            return [
                'status' => 'error',
                'message' => 'Cart tidak ditemukan',
            ];
        }

        $cart->delete();

        return [
            'status' => 'success',
            'message' => 'Cart deleted successfully',
        ];
    }
}