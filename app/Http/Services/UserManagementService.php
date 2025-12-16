<?php

namespace App\Http\Services;

use App\Constants\KeyType;
use App\Http\RedisConnection;
use Exception;

/**
 * Service for managing user data in Redis
 * 
 * Handles user creation and lookup operations.
 * Users are stored in Redis with JSON.SET and queried by email.
 */
class UserManagementService
{
    /**
     * Create a new user in Redis
     * 
     * Generates a unique user ID using Redis counter and stores user data.
     * Throws exception if email already exists.
     * 
     * @param array $data User data including email, name, password (hashed)
     * @return int The created user's ID
     * @throws Exception If user with email already exists
     */
    public static function createUser(array $data): int
    {
        // Check if user already exists with this email
        if (self::findByEmail($data['email'])) {
            throw new Exception('User already exists');
        }

        // Generate unique user ID using Redis counter
        $id = RedisConnection::incrementCounter(KeyType::User);
        $data['id'] = $id;

        // Store user data in Redis with key pattern "user:ID"
        RedisConnection::setKey(
            KeyType::User->value . ":$id",
            $data
        );

        return $id;
    }

    /**
     * Find a user by email address
     * 
     * Scans all user keys in Redis to find matching email.
     * Note: This is a full scan operation - consider using Redis secondary
     * index or hash for production use with many users.
     * 
     * @param string $email Email address to search for
     * @return array|null User data if found, null otherwise
     */
    public static function findByEmail(string $email): ?array
    {
        // Get all user keys from Redis
        $keys = RedisConnection::getKeys(KeyType::User);

        // Scan through all users to find matching email
        foreach ($keys as $key) {
            $user = RedisConnection::getKey($key);
            if ($user['email'] === $email) {
                return $user;
            }
        }

        return null;
    }
}
