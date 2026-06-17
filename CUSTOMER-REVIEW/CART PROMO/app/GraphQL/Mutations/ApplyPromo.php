<?php

namespace App\GraphQL\Mutations;

use App\Models\Promo;
use App\Services\RabbitMqPublisher;
use App\Services\SoapAuditService;
use Carbon\Carbon;

final class ApplyPromo
{
    public function __invoke($_, array $args): array
    {
        $promo = Promo::where('code', $args['code'])->first();

        if (!$promo) {
            return [
                'status' => 'error',
                'message' => 'Promo tidak ditemukan',
            ];
        }

        if ($promo->expired_at < Carbon::now()) {
            return [
                'status' => 'error',
                'message' => 'Promo expired',
            ];
        }

        if ($args['total_price'] < $promo->minimum_transaction) {
            return [
                'status' => 'error',
                'message' => 'Minimum transaksi belum memenuhi',
            ];
        }

        if ($promo->used >= $promo->max_usage) {
            return [
                'status' => 'error',
                'message' => 'Kuota promo habis',
            ];
        }

        $discount = $args['total_price'] * ($promo->discount_percent / 100);
        $promo->increment('used');

        // Send SOAP Audit for critical transaction
        try {
            $soapService = new SoapAuditService();
            $receiptNumber = $soapService->sendAudit('PromoApplied', [
                'promo_code' => $promo->code,
                'discount_percent' => $promo->discount_percent,
                'total_price' => $args['total_price'],
                'discount' => $discount,
                'final_total' => $args['total_price'] - $discount,
            ]);
        } catch (\Exception $e) {
            \Log::error('SOAP Audit failed: ' . $e->getMessage());
            $receiptNumber = null;
        }

        // Publish event to RabbitMQ
        try {
            $publisher = new RabbitMqPublisher();
            $publisher->publish('promo.applied', [
                'promo_code' => $promo->code,
                'discount' => $discount,
                'final_total' => $args['total_price'] - $discount,
            ]);
        } catch (\Exception $e) {
            \Log::error('RabbitMQ publish failed: ' . $e->getMessage());
        }

        return [
            'status' => 'success',
            'promo_code' => $promo->code,
            'discount' => $discount,
            'final_total' => $args['total_price'] - $discount,
            'receipt_number' => $receiptNumber,
        ];
    }
}