<?php

namespace App\GraphQL\Queries;

final class Products
{
    public function __invoke($_, array $args): array
    {
        return [
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'data' => [],
        ];
    }
}