<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SsoService
{
    /**
     * Cache key for JWKS public keys.
     */
    private const JWKS_CACHE_KEY = 'sso_jwks_keys';

    /**
     * Cache TTL for JWKS keys (in seconds).
     */
    private const JWKS_CACHE_TTL = 3600; // 1 hour

    /**
     * Authenticate user via SSO token endpoint.
     *
     * @param string $email    User email address
     * @param string $password User password
     * @return array JWT token response from SSO server
     *
     * @throws \RuntimeException When SSO authentication fails
     */
    public function login(string $email, string $password): array
    {
        try {
            $response = Http::post(config('iae.sso.token_url'), [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->failed()) {
                Log::warning('SSO login failed', [
                    'email' => $email,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'status' => $response->status(),
                    'message' => $response->json('message', 'Login gagal'),
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => true,
                'status' => $response->status(),
                'message' => 'Login berhasil',
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('SSO login exception', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Gagal menghubungi SSO server: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retrieve JWKS (JSON Web Key Set) from SSO server.
     * Results are cached for 1 hour to avoid excessive requests.
     *
     * @return array JWKS key set
     *
     * @throws \RuntimeException When JWKS endpoint is unreachable
     */
    public function getJwks(): array
    {
        return Cache::remember(self::JWKS_CACHE_KEY, self::JWKS_CACHE_TTL, function () {
            try {
                $response = Http::get(config('iae.sso.jwks_url'));

                if ($response->failed()) {
                    Log::error('Failed to fetch JWKS', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    throw new \RuntimeException('Gagal mengambil JWKS: HTTP ' . $response->status());
                }

                return $response->json();
            } catch (\Exception $e) {
                Log::error('JWKS fetch exception', ['error' => $e->getMessage()]);

                throw new \RuntimeException('Gagal menghubungi JWKS endpoint: ' . $e->getMessage(), 0, $e);
            }
        });
    }

    /**
     * Verify and decode a JWT token using JWKS public keys (RS256).
     *
     * @param string $token The JWT token to verify
     * @return object Decoded JWT payload
     *
     * @throws \Firebase\JWT\ExpiredException     When the token has expired
     * @throws \Firebase\JWT\SignatureInvalidException When the signature is invalid
     * @throws \UnexpectedValueException           When the token is malformed
     * @throws \RuntimeException                    When JWKS keys cannot be retrieved
     */
    public function verifyToken(string $token): object
    {
        $jwks = $this->getJwks();

        // Parse JWKS into an array of Key objects keyed by kid
        $keys = JWK::parseKeySet($jwks);

        // Decode and verify the JWT using the JWKS public keys
        $decoded = JWT::decode($token, $keys);

        return $decoded;
    }

    /**
     * Clear the cached JWKS keys.
     * Useful when keys are rotated on the SSO server.
     *
     * @return void
     */
    public function clearJwksCache(): void
    {
        Cache::forget(self::JWKS_CACHE_KEY);
    }
}
