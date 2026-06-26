<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Tugas2IntegrationTest extends TestCase
{
    use RefreshDatabase;

    private $apiKey = '102022400191';

    /**
     * Test protected routes reject missing API key.
     */
    public function test_api_key_protection_rejects_missing_key()
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Unauthorized',
                     'errors' => null
                 ]);
    }

    /**
     * Test protected routes reject invalid API key.
     */
    public function test_api_key_protection_rejects_invalid_key()
    {
        $response = $this->withHeaders([
            'X-IAE-KEY' => 'INVALID-KEY'
        ])->getJson('/api/v1/products');

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Unauthorized',
                     'errors' => null
                 ]);
    }

    /**
     * Test GET /api/v1/products with valid key.
     */
    public function test_get_all_products()
    {
        Product::create([
            'name' => 'Test Product',
            'price' => 10000,
            'stock' => 5,
            'sku' => 'TEST-001'
        ]);

        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey
        ])->getJson('/api/v1/products');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data',
                     'meta' => [
                         'service_name',
                         'api_version'
                     ]
                 ])
                 ->assertJsonFragment([
                     'status' => 'success',
                     'message' => 'Products retrieved successfully'
                 ]);
     }

    /**
     * Test GET /api/v1/products/{id} with valid key.
     */
    public function test_get_product_by_id()
    {
        $product = Product::create([
            'name' => 'Single Product',
            'price' => 20000,
            'stock' => 10,
            'sku' => 'TEST-002'
        ]);

        // Success test
        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey
        ])->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'status' => 'success',
                     'message' => 'Product retrieved successfully'
                 ]);

        // Not Found test
        $responseNotFound = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey
        ])->getJson("/api/v1/products/999");

        $responseNotFound->assertStatus(404)
                         ->assertJson([
                             'status' => 'error',
                             'message' => 'Product not found',
                             'errors' => null
                         ]);
    }

    /**
     * Test GET /api/v1/products/{id}/stock with valid key.
     */
    public function test_get_product_stock()
    {
        $product = Product::create([
            'name' => 'Stock Product',
            'price' => 12000,
            'stock' => 15,
            'sku' => 'TEST-003'
        ]);

        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey
        ])->getJson("/api/v1/products/{$product->id}/stock");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'id',
                         'stock'
                     ],
                     'meta' => [
                         'service_name',
                         'api_version'
                     ]
                 ])
                 ->assertJsonFragment([
                     'status' => 'success',
                     'message' => 'Stock retrieved successfully',
                     'data' => [
                         'id' => $product->id,
                         'stock' => 15
                     ]
                 ]);
    }

    /**
     * Test POST /api/v1/products with valid key.
     */
    public function test_create_product()
    {
        $payload = [
            'name' => 'New Product',
            'price' => 15000,
            'stock' => 20,
            'sku' => 'NEW-001'
        ];

        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey
        ])->postJson('/api/v1/products', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'status' => 'success',
                     'message' => 'Product created successfully'
                 ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'NEW-001'
        ]);
    }

    /**
     * Test GET /api/v1/products/search with valid key.
     */
    public function test_search_products()
    {
        Product::create([
            'name' => 'Searchable Item',
            'price' => 5000,
            'stock' => 50,
            'sku' => 'TEST-004'
        ]);

        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey
        ])->getJson('/api/v1/products/search?name=Searchable');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'status' => 'success',
                     'message' => 'Search successful'
                 ]);
    }

    /**
     * Test PUT /api/v1/products/{id}/update with valid key.
     */
    public function test_update_product_stock()
    {
        $product = Product::create([
            'name' => 'Update Stock Product',
            'price' => 30000,
            'stock' => 8,
            'sku' => 'TEST-005'
        ]);

        $payload = [
            'stock' => 25
        ];

        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey
        ])->putJson("/api/v1/products/{$product->id}/update", $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'product',
                         'audit' => [
                             'activity_name',
                             'receipt_number',
                             'status'
                         ]
                     ],
                     'meta' => [
                         'service_name',
                         'api_version'
                     ]
                 ])
                 ->assertJsonFragment([
                     'status' => 'success',
                     'message' => 'Stock updated successfully'
                 ]);

        $this->assertEquals(25, $product->fresh()->stock);
    }

    /**
     * Test Swagger UI documentation page is accessible.
     */
    public function test_swagger_documentation_page_is_accessible()
    {
        $response = $this->get('/api/documentation');
        $response->assertStatus(200);
    }

    /**
     * Test GraphQL Playground page is accessible.
     */
    public function test_graphql_playground_page_is_accessible()
    {
        $response = $this->get('/graphql-playground');
        $response->assertStatus(200);
    }

    /**
     * Test GraphQL query for products.
     */
    public function test_graphql_query_products()
    {
        Product::create([
            'name' => 'GQL Product',
            'price' => 9000,
            'stock' => 3,
            'sku' => 'GQL-001'
        ]);

        $query = '
            query {
                products {
                    id
                    name
                    stock
                }
            }
        ';

        $response = $this->postJson('/graphql', [
            'query' => $query
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'products' => [
                             '*' => [
                                 'id',
                                 'name',
                                 'stock'
                             ]
                         ]
                     ]
                 ]);
    }

    /**
     * Test that invalid API routes return the expected error wrapper format.
     */
    public function test_api_route_not_found_response_format()
    {
        $response = $this->getJson('/api/v1/invalid-route-name-123');

        $response->assertStatus(404)
                 ->assertExactJson([
                     'status' => 'error',
                     'message' => 'Resource not found',
                     'errors' => null
                 ]);
    }

    /**
     * Test that validation failure in POST /api/v1/products conforms to standard contract wrapper.
     */
    public function test_api_validation_error_response_format()
    {
        // Missing name, price, stock, sku in payload
        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey
        ])->postJson('/api/v1/products', []);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'errors'
                 ])
                 ->assertJsonFragment([
                     'status' => 'error'
                 ]);
    }
}
