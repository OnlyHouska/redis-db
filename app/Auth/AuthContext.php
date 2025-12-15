<?php

namespace App\Auth;

final readonly class AuthContext
{
    public function __construct(
        private int    $userId,
        private string $email,
        private string $jwt
    ) {}

    public function getUser(): int
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getJwt(): string
    {
        return $this->jwt;
    }
}
