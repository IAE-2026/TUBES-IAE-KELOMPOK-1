<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\UserRole;

class SsoService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.iae.base_url');
    }

    /**
     * Login sebagai user (warga) ke SSO Dosen,
     * tangkap JWT payload, lalu petakan ke tabel roles lokal.
     */
    public function loginAsUser(string $email, string $password): ?string
    {
        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
            'email'    => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            $token = $response->json('token');

            // Pastikan token tidak null sebelum decode
            if (!$token) {
                Log::warning('SSO login berhasil tapi token kosong', ['email' => $email]);
                return null;
            }

            // Decode JWT payload dari Cloud Dosen
            $payload = $this->decodeJwtPayload($token);

            // Log payload yang berhasil ditangkap dari SSO
            Log::info('SSO JWT Payload captured', [
                'sub'   => $payload['sub'] ?? null,
                'email' => $payload['email'] ?? $email,
                'role'  => $payload['role'] ?? 'not provided',
                'iat'   => $payload['iat'] ?? null,
                'exp'   => $payload['exp'] ?? null,
            ]);

            // Petakan user ke tabel roles lokal berdasarkan JWT payload
            UserRole::updateOrCreate(
                ['email' => $email],
                [
                    'sso_user_id' => $payload['sub'] ?? null,
                    'role'        => $payload['role'] ?? $payload['groups'][0] ?? 'customer',
                    'jwt_token'   => $token,
                ]
            );

            return $token;
        }

        Log::error('SSO login gagal', [
            'email'  => $email,
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        return null;
    }

    /**
     * Login sebagai Machine-to-Machine (M2M) menggunakan API Key.
     * Digunakan untuk autentikasi SOAP Audit dan RabbitMQ.
     */
    public function loginAsM2M(): ?string
    {
        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
            'api_key' => config('services.iae.api_key'),
            'nim'     => config('services.iae.nim'),
        ]);

        if ($response->successful()) {
            return $response->json('token');
        }

        Log::error('SSO M2M login gagal', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        return null;
    }

    /**
     * Decode JWT payload (bagian kedua dari token JWT).
     * Mengekstrak informasi user seperti sub, email, role, dll.
     */
    public function decodeJwtPayload(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) < 2) {
            Log::warning('Invalid JWT format: kurang dari 2 segmen');
            return [];
        }

        $payload = base64_decode(str_pad(
            strtr($parts[1], '-_', '+/'),
            strlen($parts[1]) % 4,
            '=',
            STR_PAD_RIGHT
        ));

        return json_decode($payload, true) ?? [];
    }
}