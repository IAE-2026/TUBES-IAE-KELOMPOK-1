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
 *     security={{"ApiKeyAuth": {}}, {"BearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Products retrieved successfully"),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="service_name", type="string", example="Product-Service"),
 *                 @OA\Property(property="api_version", type="string", example="v1")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
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

/**
 * @OA\Get(
 *     path="/api/v1/products/{id}",
 *     operationId="getProductById",
 *     tags={"Products"},
 *     summary="Get product by ID",
 *     description="Returns a single product details",
 *     security={{"ApiKeyAuth": {}}, {"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Product retrieved successfully"),
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="service_name", type="string", example="Product-Service"),
 *                 @OA\Property(property="api_version", type="string", example="v1")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Product not found"),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     )
 * )
 */
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

/**
 * @OA\Post(
 *     path="/api/v1/products",
 *     operationId="storeProduct",
 *     tags={"Products"},
 *     summary="Create a new product",
 *     description="Creates a new product in the store",
 *     security={{"ApiKeyAuth": {}}, {"BearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Product details",
 *         @OA\JsonContent(
 *             required={"name","price","stock","sku"},
 *             @OA\Property(property="name", type="string", example="Produk A"),
 *             @OA\Property(property="price", type="number", format="float", example=15000.00),
 *             @OA\Property(property="stock", type="integer", example=10),
 *             @OA\Property(property="sku", type="string", example="PRD-001"),
 *             @OA\Property(property="description", type="string", example="Deskripsi Produk A")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Product created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Product created successfully"),
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="service_name", type="string", example="Product-Service"),
 *                 @OA\Property(property="api_version", type="string", example="v1")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Unprocessable Entity (Validation failed)",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="The sku field is required."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
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

/**
 * @OA\Get(
 *     path="/api/v1/products/search",
 *     operationId="searchProducts",
 *     tags={"Products"},
 *     summary="Search products by name",
 *     description="Returns a list of products matching the name parameter",
 *     security={{"ApiKeyAuth": {}}, {"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="name",
 *         in="query",
 *         required=true,
 *         description="Product name search term",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Search successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Search successful"),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="service_name", type="string", example="Product-Service"),
 *                 @OA\Property(property="api_version", type="string", example="v1")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     )
 * )
 */
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

/**
 * @OA\Get(
 *     path="/api/v1/products/{id}/stock",
 *     operationId="getProductStock",
 *     tags={"Products"},
 *     summary="Get product stock",
 *     description="Returns stock information for a specific product ID",
 *     security={{"ApiKeyAuth": {}}, {"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Stock retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Stock retrieved successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="stock", type="integer", example=15)
 *             ),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="service_name", type="string", example="Product-Service"),
 *                 @OA\Property(property="api_version", type="string", example="v1")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Product not found"),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     )
 * )
 */
    public function stock($id)
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
            'stock' => $product->stock
        ],
        'meta' => [
            'service_name' => 'Product-Service',
            'api_version' => 'v1'
        ]
    ]);
}

/**
 * @OA\Put(
 *     path="/api/v1/products/{id}/update",
 *     operationId="updateProductStock",
 *     tags={"Products"},
 *     summary="Update product stock",
 *     description="Updates the stock quantity of a specific product",
 *     security={{"ApiKeyAuth": {}}, {"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Stock value",
 *         @OA\JsonContent(
 *             required={"stock"},
 *             @OA\Property(property="stock", type="integer", example=25)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Stock updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Stock updated successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="product", type="object"),
 *                 @OA\Property(property="audit", type="object",
 *                     @OA\Property(property="activity_name", type="string", example="StockUpdated"),
 *                     @OA\Property(property="receipt_number", type="string", example="REC123456"),
 *                     @OA\Property(property="status", type="string", example="SUCCESS")
 *                 )
 *             ),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="service_name", type="string", example="Product-Service"),
 *                 @OA\Property(property="api_version", type="string", example="v1")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Product not found"),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     )
 * )
 */
    // UPDATE STOCK
   public function updateStock(Request $request, $id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found',
            'errors' => null
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
        'data' => [
            'product' => $product,
            'audit' => [
                'activity_name' => 'StockUpdated',
                'receipt_number' => $receiptNumber,
                'status' => $receiptNumber ? 'SUCCESS' : 'FAILED'
            ]
        ],
        'meta' => [
            'service_name' => 'Product-Service',
            'api_version' => 'v1'
        ]
    ]);
}

        
    }
}
// last updated: 2026-06-26
