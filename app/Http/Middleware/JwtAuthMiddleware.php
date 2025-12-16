<?php

namespace App\Http\Middleware;

use App\Auth\AuthContext;
use App\Http\RedisConnection;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for JWT token authentication
 * 
 * Validates JWT tokens from Authorization header and creates AuthContext
 * for authenticated requests. Checks token blacklist for revoked tokens.
 */
class JwtAuthMiddleware
{
    /**
     * Handle an incoming request
     * 
     * Validates JWT token and creates AuthContext if valid.
     * Rejects requests with missing, invalid, expired, or revoked tokens.
     * 
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the chain
     * @return JsonResponse|Response Response or passes to next middleware
     */
    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        // Get Authorization header
        $authHeader = $request->header('Authorization');

        // Check if Authorization header exists and has Bearer prefix
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Missing token'], 401);
        }

        // Extract token from "Bearer <token>" format
        $token = substr($authHeader, 7);

        try {
            // Check if token is blacklisted (revoked during logout)
            if (RedisConnection::keyExists("jwt:blacklist:$token")) {
                return response()->json(['error' => 'Token revoked'], 401);
            }

            // Decode and validate JWT token
            $decoded = JWT::decode(
                $token,
                new Key(config('app.key'), 'HS256')
            );

            // Create AuthContext with user information from token
            $authContext = new AuthContext(
                userId: (int) $decoded->sub,
                email: (string) $decoded->email,
                jwt: $token
            );

            // Register AuthContext in service container for dependency injection
            app()->instance(AuthContext::class, $authContext);
            
        } catch (Exception) {
            // Token is invalid, expired, or malformed
            return response()->json([
                'error' => 'Invalid or expired token',
            ], 401);
        }

        // Pass request to next middleware with authenticated context
        return $next($request);
    }
}
