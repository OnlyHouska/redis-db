<?php

namespace App\Http;

use App\Constants\KeyType;
use App\Constants\SubkeyType;
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
        return $result ? (json_decode($result, true) ?? []) : [];
    }

    public static function setKey(string $key, array $data, ?int $ttl = null): bool
    {
        $result = self::getClient()->rawcommand('JSON.SET', $key, '$', json_encode($data));

        if ($result && $ttl !== null && $ttl >= -1) {
            self::getClient()->expire($key, $ttl);
        }

        return (bool) $result;
    }

    public static function delKey(string $key): bool
    {
        if (!self::keyExists($key)) {
            return false;
        }

        return (bool) self::getClient()->del($key);
    }

    public static function keyExists(string $key): bool
    {
        return (bool) self::getClient()->exists($key);
    }

    public static function incrementCounter(KeyType $type): int
    {
        return (int) self::getClient()->incr("$type->value:" . SubkeyType::Counter->value);
    }

    public static function getKeys(KeyType $type, ?int $id = null): array
    {
        $subpattern = $id ?? '*';

        $keys = self::getClient()->keys("$type->value:$subpattern") ?: [];
        return array_filter($keys, fn($k) => $k !== "$type->value:" . SubkeyType::Counter->value);
    }

    /**
     * Redis Streams helpers
     */
    public static function addToStream(KeyType $type, array $data): string
    {
        $args = [];
        foreach ($data as $field => $value) {
            $args[] = (string) $field;
            $args[] = is_scalar($value) || $value === null ? (string) ($value ?? '') : json_encode($value);
        }

        return (string) self::getClient()->rawcommand('XADD', "stream:$type->value-events", '*', ...$args);
    }

    public static function rangeStream(KeyType $type, string $start = '-', string $end = '+', int $count = 100): array
    {
        $result = self::getClient()->rawcommand('XRANGE', "stream:$type->value-events", $start, $end, 'COUNT', $count);
        return is_array($result) ? $result : [];
    }
}
