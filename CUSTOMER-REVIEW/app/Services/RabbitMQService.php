<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RabbitMQService
{
    protected string $publishUrl;

    public function __construct()
    {
        $this->publishUrl = config('services.iae.base_url') . '/api/v1/messages/publish';
    }

    /**
     * Publish event notification review ke RabbitMQ Dosen.
     * Event dikirim dalam format JSON melalui HTTP API.
     */
    public function publishReviewEvent(array $reviewData): bool
    {
        // RabbitMQ butuh M2M token
        $sso = new SsoService();
        $m2mToken = $sso->loginAsM2M();

        if (!$m2mToken) {
            Log::error('RabbitMQ publish gagal: M2M token tidak tersedia');
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$m2mToken}",
            'Content-Type'  => 'application/json',
        ])->post($this->publishUrl, [
            'routing_key' => 'review.submitted',
            'message' => [
                'event' => 'review.submitted',
                'data'  => [
                    'product_id'    => $reviewData['product_id'],
                    'reviewer_name' => $reviewData['reviewer_name'],
                    'rating'        => $reviewData['rating'],
                ],
            ],
        ]);

        if ($response->successful()) {
            Log::info('RabbitMQ event published', [
                'event'      => 'review.submitted',
                'product_id' => $reviewData['product_id'],
            ]);
            return true;
        }

        Log::error('RabbitMQ publish gagal', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        return false;
    }
}