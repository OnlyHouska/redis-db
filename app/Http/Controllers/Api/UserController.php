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

/**
 * API Controller for user authentication and management
 *
 * Handles user registration, login, logout, and profile retrieval
 * using JWT tokens for authentication.
 */
class UserController extends Controller
{
    /**
     * Register a new user
     *
     * Creates a new user account and returns a JWT token for immediate authentication
     *
     * @param Request $request HTTP request with user registration data
     * @return JsonResponse User data with JWT token or error message
     */
    public function register(Request $request): JsonResponse
    {
        // Validate registration data
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
            'name'     => 'required|string|max:255',
        ]);

        try {
            // Prepare user data with hashed password
            $user = [
                'email'      => $validated['email'],
                'name'       => $validated['name'],
                'password'   => Hash::make($validated['password']),
                'created_at' => now()->toISOString(),
            ];

            // Create user in Redis (throws exception if email already exists)
            $id = UserManagementService::createUser($user);

            // Generate JWT token for the new user
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

    /**
     * Authenticate a user and return JWT token
     *
     * @param Request $request HTTP request with login credentials
     * @return JsonResponse User data with JWT token or error message
     */
    public function login(Request $request): JsonResponse
    {
        // Validate login credentials
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            // Find user by email
            $user = UserManagementService::findByEmail($validated['email']);

            // Verify user exists and password is correct
            if (!$user || !Hash::check($validated['password'], $user['password'])) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Generate JWT token for authenticated user
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

    /**
     * Generate a JWT token for a user
     *
     * Token is valid for 24 hours and includes user ID and email
     *
     * @param int $userId User ID
     * @param string $email User email
     * @return string JWT token
     */
    private function generateJwt(int $userId, string $email): string
    {
        // Build JWT payload
        $payload = [
            'iss' => config('app.url'),      // Issuer
            'sub' => $userId,                 // Subject (user ID)
            'email' => $email,                // User email
            'iat' => time(),                  // Issued at
            'exp' => time() + 60 * 60 * 24    // Expiration (24 hours)
        ];

        // Encode and return JWT
        return JWT::encode($payload, config('app.key'), 'HS256');
    }

    /**
     * Get current authenticated user's profile
     *
     * Requires valid JWT token in Authorization header
     *
     * @param Request $request HTTP request with user context
     * @return JsonResponse User profile data
     */
    public function me(Request $request): JsonResponse
    {
        // Return user data from auth context (set by JWT middleware)
        return response()->json([
            'id'    => $request->get('user_id'),
            'email' => $request->get('email'),
        ]);
    }

    /**
     * Logout user by blacklisting their JWT token
     *
     * Adds the token to a Redis blacklist with 24h TTL
     *
     * @param Request $request HTTP request with JWT token
     * @return JsonResponse Success message or error
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Get JWT token from request context
            $token = $request->get('jwt');

            if (!$token) {
                return response()->json(['error' => 'Token missing'], 400);
            }

            // Add token to blacklist in Redis (expires in 24h)
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
