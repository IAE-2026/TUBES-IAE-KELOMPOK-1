<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Customer Review Service API",
    version: "1.0.0",
    description: "API untuk mengelola review produk"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "X-API-KEY"
)]
class SwaggerInfo {}