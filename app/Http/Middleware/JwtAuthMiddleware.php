<?php

namespace App\Http\Middleware;

use App\Http\RedisConnection;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Missing token'], 401);
        }

        $token = substr($authHeader, 7);

        try {
            if (RedisConnection::keyExists("jwt:blacklist:$token")) {
                return response()->json(['error' => 'Token revoked'], 401);
            }

            $decoded = JWT::decode(
                $token,
                new Key(config('app.key'), 'HS256')
            );

            $request->attributes->add([
                'user_id' => $decoded->sub,
                'email'   => $decoded->email,
                'jwt'     => $token,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Invalid or expired token',
            ], 401);
        }

        return $next($request);
    }
}
