<?php

namespace App\Http\Controllers\Api;

use App\Services\SsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(
        protected SsoService $ssoService
    ) {}

    /**
     * Login via SSO.
     *
     * Accepts email and password, forwards to SSO token endpoint,
     * and returns the JWT token response.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->ssoService->login(
                $request->input('email'),
                $request->input('password')
            );

            if (!$result['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'],
                    'data' => $result['data'],
                ], $result['status']);
            }

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Get authenticated user info.
     *
     * Returns the decoded SSO user payload and local role
     * for the currently authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $ssoUser = $request->attributes->get('sso_user');
        $localRole = $request->attributes->get('local_role');

        return response()->json([
            'status' => 'success',
            'message' => 'Data pengguna berhasil diambil',
            'data' => [
                'sso_user' => $ssoUser,
                'local_role' => $localRole,
            ],
        ]);
    }
}
