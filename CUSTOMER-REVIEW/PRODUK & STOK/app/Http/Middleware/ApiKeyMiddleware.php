<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;



class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
public function handle($request, Closure $next)
{
    $apiKey = $request->header('X-IAE-KEY');

    if ($apiKey !== env('IAE_API_KEY')) {

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
            'errors' => null
        ], 401);

    }

    return $next($request);
}
}
