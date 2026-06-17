<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-IAE-KEY');
        $expectedKey = config('app.api_key');

        if (!$apiKey || $apiKey !== $expectedKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing API Key.'
            ], 401);
        }

        return $next($request);
    }
}