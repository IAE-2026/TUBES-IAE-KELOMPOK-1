<?php

namespace App\GraphQL\Queries;

final class Hello
{
    public function __invoke($_, array $args): string
    {
        return 'Hello GraphQL';
    }
}