<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = trim($request->header('X-IAE-KEY'));

        if ($apiKey != "102022400283") {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized API Key'
            ], 401);
        }

        return $next($request);
    }
}