<?php

namespace App\GraphQL\Queries;

use App\Models\Promo;

final class Promos
{
    public function __invoke($_, array $args): array
    {
        return [
            'status' => 'success',
            'message' => 'Promos retrieved successfully',
            'data' => Promo::all(),
        ];
    }
}