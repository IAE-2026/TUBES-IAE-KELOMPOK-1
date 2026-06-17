<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\SsoService;
use App\Services\SoapAuditService;
use App\Services\RabbitMQService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReviewController extends Controller
{
    #[OA\Get(
        path: "/api/v1/reviews",
        summary: "Ambil semua review",
        security: [["ApiKeyAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function index()
    {
        $reviews = Review::all();
        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }

    #[OA\Get(
        path: "/api/v1/reviews/product/{product_id}",
        summary: "Ambil review berdasarkan produk",
        security: [["ApiKeyAuth" => []]],
        parameters: [
            new OA\Parameter(name: "product_id", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function byProduct($product_id)
    {
        $reviews = Review::where('product_id', $product_id)->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reviews found for this product'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }

    #[OA\Post(
        path: "/api/v1/reviews",
        summary: "Simpan review baru",
        security: [["ApiKeyAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["product_id", "reviewer_name", "rating", "comment"],
                properties: [
                    new OA\Property(property: "product_id", type: "string", example: "PROD-001"),
                    new OA\Property(property: "reviewer_name", type: "string", example: "Azzahra"),
                    new OA\Property(property: "rating", type: "integer", example: 5),
                    new OA\Property(property: "comment", type: "string", example: "Produk bagus!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'    => 'required|string',
            'reviewer_name' => 'required|string',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'required|string',
        ]);

        // 1. Simpan review ke DB lokal
        $review = Review::create($validated);

        // 2. Login SSO dosen pakai akun warga
        $sso   = new SsoService();
        $token = $sso->loginAsUser(
            config('services.iae.sso_email'),
            config('services.iae.sso_password')
        );
        \Log::info('SSO Token: ' . ($token ? 'OK' : 'NULL - Login gagal'));

        $receiptNumber = null;
        $mqSuccess     = false;

        if ($token) {
            // 3. Kirim SOAP Audit
            $soap = new SoapAuditService();
            $receiptNumber = $soap->sendReviewAudit([
                'product_id'    => $validated['product_id'],
                'reviewer_name' => $validated['reviewer_name'],
                'rating'        => $validated['rating'],
                'comment'       => $validated['comment'],
            ]);

            // 4. Publish ke RabbitMQ
            $mq = new RabbitMQService();
            $mqSuccess = $mq->publishReviewEvent([
                'product_id'    => $validated['product_id'],
                'reviewer_name' => $validated['reviewer_name'],
                'rating'        => $validated['rating'],
            ]);
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Review created successfully',
            'data'           => $review,
            'receipt_number' => $receiptNumber,
            'mq_published'   => $mqSuccess,
        ], 201);
    }
}