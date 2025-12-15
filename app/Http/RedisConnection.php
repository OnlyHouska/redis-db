<?php

namespace App\Http;

use App\Constants\KeyType;
use App\Constants\SubkeyType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

final class RedisConnection
{
    private static function getClient(): \Redis
    {
        return Redis::connection()->client();
    }

    public static function getKey(string $key): array
    {
        $result = self::getClient()->rawcommand('JSON.GET', $key);
        return json_decode($result, true);
    }

    public static function setKey(string $key, array $data, ?int $ttl = null): bool
    {
        $result = self::getClient()->rawcommand('JSON.SET', $key, '$', json_encode($data));

        if ($result && $ttl >= -1) {
            self::getClient()->expire($key, $ttl);
        }

        return $result;
    }

    public static function delKey(string $key): bool
    {
        if (!self::keyExists($key)) {
            return false;
        }

        return self::getClient()->del($key);
    }

    public static function keyExists(string $key): bool
    {
        return self::getClient()->exists($key);
    }

    public static function incrementCounter(KeyType $type): int
    {
        return self::getClient()->incr("$type->value:" . SubkeyType::Counter->value);
    }

    public static function getKeys(KeyType $type, ?int $id = null): array
    {
        $subpattern = $id ?? '*';

        $keys = self::getClient()->keys("$type->value:$subpattern");
        return array_filter($keys, fn($k) => $k != "$type->value:" . SubkeyType::Counter->value);
    }


}
