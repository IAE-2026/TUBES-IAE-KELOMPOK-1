<?php

namespace App\Http\Controllers\Api;

use App\Models\Promo;
use App\Services\RabbitMqPublisher;
use App\Services\SoapAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Cart Promo Service API',
    version: '1.0.0',
    description: 'API Documentation for Cart Promo Service'
)]
class PromoController extends Controller
{
    #[OA\Get(
        path: '/api/v1/promo',
        operationId: 'getPromos',
        tags: ['Promo'],
        summary: 'Get list promos',
        responses: [
            new OA\Response(response: 200, description: 'Promos retrieved successfully'),
        ]
    )]
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Promos retrieved successfully',
            'data' => Promo::all(),
        ]);
    }

    #[OA\Get(
        path: '/api/v1/promo/{id}',
        operationId: 'getPromoDetail',
        tags: ['Promo'],
        summary: 'Get promo detail',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Promo detail'),
            new OA\Response(response: 404, description: 'Promo not found'),
        ]
    )]
    public function show($id)
    {
        $promo = Promo::find($id);

        if (!$promo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Promo tidak ditemukan',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Promo detail',
            'data' => $promo,
        ]);
    }

    #[OA\Post(
        path: '/api/v1/promo/apply',
        operationId: 'applyPromo',
        tags: ['Promo'],
        summary: 'Apply promo code',
        description: 'Applies a promo code to a cart total',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code', 'total_price'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'PROMO10'),
                    new OA\Property(property: 'total_price', type: 'number', format: 'float', example: 100000),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Promo applied successfully'),
            new OA\Response(response: 400, description: 'Promo cannot be applied'),
            new OA\Response(response: 404, description: 'Promo not found'),
        ]
    )]
    public function apply(Request $request)
    {
        $promo = Promo::where('code', $request->code)->first();

        if (!$promo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Promo tidak ditemukan'
            ], 404);
        }

        if ($promo->expired_at < Carbon::now()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Promo expired'
            ], 400);
        }

        if ($request->total_price < $promo->minimum_transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimum transaksi belum memenuhi'
            ], 400);
        }

        if ($promo->used >= $promo->max_usage) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kuota promo habis'
            ], 400);
        }

        $discount = $request->total_price * ($promo->discount_percent / 100);
        $promo->increment('used');

        // Send SOAP Audit for critical transaction
        try {
            $soapService = new SoapAuditService();
            $receiptNumber = $soapService->sendAudit('PromoApplied', [
                'promo_code' => $promo->code,
                'discount_percent' => $promo->discount_percent,
                'total_price' => $request->total_price,
                'discount' => $discount,
                'final_total' => $request->total_price - $discount,
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
                'final_total' => $request->total_price - $discount,
            ]);
        } catch (\Exception $e) {
            \Log::error('RabbitMQ publish failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => null,
            'promo_code' => $promo->code,
            'discount' => $discount,
            'final_total' => $request->total_price - $discount,
            'receipt_number' => $receiptNumber,
        ]);
    }
}