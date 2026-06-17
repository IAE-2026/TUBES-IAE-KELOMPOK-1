<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RabbitMqPublisher
 *
 * Publishes events to the central IAE RabbitMQ message broker via HTTP API.
 * Uses a fire-and-forget pattern — errors are logged but never thrown.
 */
class RabbitMqPublisher
{
    /**
     * Cache key for the SSO bearer token used by RabbitMQ publisher.
     */
    private const TOKEN_CACHE_KEY = 'rabbitmq_publisher_sso_token';

    /**
     * Cache TTL for the SSO token (in seconds).
     */
    private const TOKEN_CACHE_TTL = 3300; // 55 minutes

    /**
     * Publish an event to the RabbitMQ HTTP API.
     *
     * @param string $eventType The event type identifier (e.g., 'cart.created', 'promo.applied')
     * @param array  $payload   The event payload data
     * @return bool True on success, false on failure
     */
    public function publish(string $eventType, array $payload): bool
    {
        try {
            $token = $this->getToken();

            $body = [
                'event_type' => $eventType,
                'team_id' => config('iae.team_id'),
                'timestamp' => now()->toIso8601String(),
                'payload' => $payload,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->timeout(15)
              ->post(config('iae.rabbitmq.publish_url'), $body);

            if ($response->failed()) {
                Log::error('RabbitMQ publish failed', [
                    'event_type' => $eventType,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            Log::info('RabbitMQ event published', [
                'event_type' => $eventType,
                'team_id' => config('iae.team_id'),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('RabbitMQ publish exception', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get a valid SSO bearer token, using cache when available.
     *
     * Authenticates via SsoService::login() using credentials from config.
     *
     * @return string The bearer token
     *
     * @throws \RuntimeException When SSO authentication fails
     */
    public function getToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_CACHE_TTL, function () {
            $ssoService = new SsoService();

            $result = $ssoService->login(
                config('iae.sso.email'),
                config('iae.sso.password')
            );

            if (!$result['success']) {
                throw new \RuntimeException('Gagal mendapatkan token SSO untuk RabbitMQ: ' . ($result['message'] ?? 'Unknown error'));
            }

            // Extract the access token from the SSO response
            $token = $result['data']['access_token']
                ?? $result['data']['token']
                ?? null;

            if (!$token) {
                throw new \RuntimeException('Token tidak ditemukan dalam response SSO');
            }

            Log::info('RabbitMQ Publisher: SSO token acquired and cached');

            return $token;
        });
    }
}
