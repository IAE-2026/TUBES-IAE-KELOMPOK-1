<?php
namespace App\Http\Controllers\Api;
use OpenApi\Annotations as OA;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\CentralSSOService;

class ProductController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/v1/products",
 *     operationId="getProducts",
 *     tags={"Products"},
 *     summary="Get list products",
 *     description="Returns list of products",
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation"
 *     )
 * )
 */
    // GET ALL PRODUCTS
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'data' => Product::all(),
            'meta' => [
                'service_name' => 'Product-Service',
                'api_version' => 'v1'
            ]
        ]);
    }

    // GET PRODUCT BY ID
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
                'errors' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product retrieved successfully',
            'data' => $product,
            'meta' => [
                'service_name' => 'Product-Service',
                'api_version' => 'v1'
            ]
        ]);
    }

    public function getStock($id)
{
    $product = Product::find($id);

    if (!$product) {

        return response()->json([
            'status' => 'error',
            'message' => 'Product not found',
            'errors' => null
        ], 404);

    }

    return response()->json([
        'status' => 'success',
        'message' => 'Stock retrieved successfully',
        'data' => [
            'id' => $product->id,
            'name' => $product->name,
            'stock' => $product->stock
        ],
        'meta' => [
            'service_name' => 'Product-Service',
            'api_version' => 'v1'
        ]
    ]);
}

    // CREATE PRODUCT
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'sku' => 'required|unique:products'
        ]);

        $product = Product::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product,
            'meta' => [
                'service_name' => 'Product-Service',
                'api_version' => 'v1'
            ]
        ], 201);
    }

    // SEARCH PRODUCT
    public function search(Request $request)
    {
        $products = Product::where(
            'name',
            'LIKE',
            '%' . $request->name . '%'
        )->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Search successful',
            'data' => $products,
            'meta' => [
                'service_name' => 'Product-Service',
                'api_version' => 'v1'
            ]
        ]);
    }

    public function stock($id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found'
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'stock' => $product->stock
    ]);
}

    // UPDATE STOCK
   public function updateStock(Request $request, $id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found'
        ], 404);
    }

    $oldStock = $product->stock;
    $product->stock = $request->stock;
    $product->save();

    // Prepare audit content
    $logContent = [
        'product_id' => $product->id,
        'product_name' => $product->name,
        'sku' => $product->sku,
        'old_stock' => $oldStock,
        'new_stock' => $product->stock,
        'updated_by' => auth()->user() ? auth()->user()->email : 'system',
    ];

    // SOAP Audit (Modul 2)
    $receiptNumber = CentralSSOService::audit('StockUpdated', $logContent);

    // Save Audit Log
    DB::table('audit_logs')->insert([
        'activity_name' => 'StockUpdated',
        'log_content' => json_encode($logContent),
        'receipt_number' => $receiptNumber,
        'status' => $receiptNumber ? 'SUCCESS' : 'FAILED',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // AMQP Publisher (Modul 3)
    CentralSSOService::publishMessage($logContent);

    return response()->json([
        'status' => 'success',
        'message' => 'Stock updated successfully',
        'data' => $product,
        'audit' => [
            'activity_name' => 'StockUpdated',
            'receipt_number' => $receiptNumber,
            'status' => $receiptNumber ? 'SUCCESS' : 'FAILED'
        ]
    ]);
}

        
    }
