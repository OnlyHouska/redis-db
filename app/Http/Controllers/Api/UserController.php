<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\RedisConnection;
use App\Http\Services\UserManagementService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;

class UserController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
            'name'     => 'required|string|max:255',
        ]);

        try {
            $user = [
                'email'      => $validated['email'],
                'name'       => $validated['name'],
                'password'   => Hash::make($validated['password']),
                'created_at' => now()->toISOString(),
            ];

            $id = UserManagementService::createUser($user);

            $token = $this->generateJwt($id, $user['email']);

            return response()->json([
                'token' => $token,
                'user'  => [
                    'id'    => $id,
                    'email' => $user['email'],
                    'name'  => $user['name'],
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $user = UserManagementService::findByEmail($validated['email']);

            if (!$user || !Hash::check($validated['password'], $user['password'])) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $token = $this->generateJwt($user['id'], $user['email']);

            return response()->json([
                'token' => $token,
                'user'  => [
                    'id'    => $user['id'],
                    'email' => $user['email'],
                    'name'  => $user['name'],
                ]
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateJwt(int $userId, string $email): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $userId,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 // 24h
        ];

        return JWT::encode($payload, config('app.key'), 'HS256');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'id'    => $request->get('user_id'),
            'email' => $request->get('email'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->get('jwt');

            if (!$token) {
                return response()->json(['error' => 'Token missing'], 400);
            }

            RedisConnection::setKey(
                "jwt:blacklist:$token",
                ['revoked' => true],
                60 * 60 * 24
            );

            return response()->json([
                'message' => 'Successfully logged out',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
