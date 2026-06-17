<?php

namespace App\GraphQL\Types;

use App\Models\Product;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ProductType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Product',
        'model' => Product::class
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int()
            ],
            'name' => [
                'type' => Type::string()
            ],
            'description' => [
                'type' => Type::string()
            ],
            'price' => [
                'type' => Type::float()
            ],
            'stock' => [
                'type' => Type::int()
            ],
            'sku' => [
                'type' => Type::string()
            ],
        ];
    }
}