<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\JwtVerifier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class SsoAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Missing or invalid Authorization header.',
                'errors' => null
            ], 401);
        }

        $jwt = substr($authorizationHeader, 7);

        try {
            // Verify JWT
            $payload = JwtVerifier::verify($jwt);

            // Determine user information and role
            $tokenType = $payload['token_type'] ?? 'user';
            $sub = $payload['sub'] ?? '';

            if (!$sub) {
                throw new Exception("JWT is missing 'sub' claim.");
            }

            $email = $sub;
            $name = 'SSO User';
            $roleName = 'warga';

            if ($tokenType === 'user') {
                $profile = $payload['profile'] ?? [];
                $name = $profile['name'] ?? 'Warga SSO';
                $email = $profile['email'] ?? $sub;
                $roleName = 'warga';
            } elseif ($tokenType === 'm2m') {
                $app = $payload['app'] ?? [];
                $name = $app['name'] ?? 'M2M App';
                $roleName = 'm2m';
            }

            // Sync/Create User in local DB
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt('sso-managed-password-' . rand(1000, 9999)),
                ]);
            } else {
                // Keep name up to date
                $user->name = $name;
                $user->save();
            }

            // Find or create local Role
            $role = DB::table('roles')->where('name', $roleName)->first();
            if (!$role) {
                $roleId = DB::table('roles')->insertGetId([
                    'name' => $roleName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $roleId = $role->id;
            }

            // Attach role if not already attached
            $hasRole = DB::table('role_user')
                ->where('user_id', $user->id)
                ->where('role_id', $roleId)
                ->exists();

            if (!$hasRole) {
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Authenticate in Laravel request lifecycle
            auth()->login($user);

            // Store token payload in request for controllers to access if needed
            $request->attributes->set('sso_payload', $payload);
            $request->attributes->set('sso_user', $user);
            $request->attributes->set('sso_role', $roleName);

            return $next($request);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: ' . $e->getMessage(),
                'errors' => null
            ], 401);
        }
    }
}
