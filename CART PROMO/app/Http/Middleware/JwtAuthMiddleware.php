<?php

namespace App\Http\Middleware;

use App\Models\LocalRole;
use App\Services\SsoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    public function __construct(
        protected SsoService $ssoService
    ) {}

    /**
     * Handle an incoming request.
     *
     * Extracts the Bearer token from the Authorization header,
     * verifies it via the SSO service, and attaches the decoded
     * user payload and local role to the request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak ditemukan. Silakan login terlebih dahulu.',
            ], 401);
        }

        try {
            // Verify the JWT token using SSO JWKS public keys
            $decoded = $this->ssoService->verifyToken($token);

            // Attach decoded SSO user payload to request
            $request->attributes->set('sso_user', $decoded);

            // Look up local role by SSO email
            $email = $decoded->sub ?? $decoded->email ?? null;
            $localRole = null;

            if ($email) {
                $localRole = LocalRole::where('sso_email', $email)->first();
            }

            // Attach local role to request (null if not found)
            $request->attributes->set('local_role', $localRole);

            return $next($request);
        } catch (\Firebase\JWT\ExpiredException $e) {
            Log::warning('JWT token expired', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Token telah kedaluwarsa. Silakan login kembali.',
            ], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            Log::warning('JWT signature invalid', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid.',
            ], 401);
        } catch (\Exception $e) {
            Log::error('JWT verification failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Autentikasi gagal: ' . $e->getMessage(),
            ], 401);
        }
    }
}
