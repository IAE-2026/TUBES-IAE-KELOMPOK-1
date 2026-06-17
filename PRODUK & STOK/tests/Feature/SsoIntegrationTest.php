<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SsoIntegrationTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_sso_middleware_rejects_missing_token()
    {
        $response = $this->getJson('/api/v1/sso/me');
        $response->assertStatus(401)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonFragment(['message' => 'Unauthorized: Missing or invalid Authorization header.']);
    }

    public function test_sso_middleware_rejects_invalid_jwt_format()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_here'
        ])->getJson('/api/v1/sso/me');

        $response->assertStatus(401)
                 ->assertJsonPath('status', 'error');
    }
}
