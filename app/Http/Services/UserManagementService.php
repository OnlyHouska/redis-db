<?php

namespace App\Http\Services;

use App\Constants\KeyType;
use App\Http\RedisConnection;
use Exception;

class UserManagementService
{
    /**
     * @throws Exception
     */
    public static function createUser(array $data): int
    {
        if (self::findByEmail($data['email'])) {
            throw new Exception('User already exists');
        }

        $id = RedisConnection::incrementCounter(KeyType::User);
        $data['id'] = $id;

        RedisConnection::setKey(
            KeyType::User->value . ":$id",
            $data
        );

        return $id;
    }

    public static function findByEmail(string $email): ?array
    {
        $keys = RedisConnection::getKeys(KeyType::User);

        foreach ($keys as $key) {
            $user = RedisConnection::getKey($key);
            if ($user['email'] === $email) {
                return $user;
            }
        }

        return null;
    }
}
