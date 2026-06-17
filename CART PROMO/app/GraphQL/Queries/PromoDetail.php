<?php

namespace App\GraphQL\Queries;

use App\Models\Promo;

final class PromoDetail
{
    public function __invoke($_, array $args): array
    {
        $promo = Promo::find($args['id']);

        if (!$promo) {
            return [
                'status' => 'error',
                'message' => 'Promo tidak ditemukan',
                'data' => null,
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Promo detail',
            'data' => $promo,
        ];
    }
}