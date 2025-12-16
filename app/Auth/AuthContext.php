<?php

namespace App\Auth;

/**
 * Authentication context for the current request
 * 
 * Immutable value object containing authenticated user information.
 * Created by JwtAuthMiddleware after successful token validation and
 * injected into services that require user context.
 */
final readonly class AuthContext
{
    /**
     * Create an authentication context
     * 
     * @param int $userId Authenticated user's ID
     * @param string $email Authenticated user's email
     * @param string $jwt The JWT token used for authentication
     */
    public function __construct(
        private int    $userId,
        private string $email,
        private string $jwt
    ) {}

    /**
     * Get the authenticated user's ID
     * 
     * @return int User ID
     */
    public function getUser(): int
    {
        return $this->userId;
    }

    /**
     * Get the authenticated user's email
     * 
     * @return string User email address
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get the JWT token for this request
     * 
     * Useful for operations like logout that need to blacklist the token
     * 
     * @return string JWT token
     */
    public function getJwt(): string
    {
        return $this->jwt;
    }
}
