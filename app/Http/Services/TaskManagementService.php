<?php

namespace App\Http\Services;

use App\Constants\KeyType;
use App\Http\RedisConnection;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskManagementService
{
    public static function getTasks(?int $taskId = null): array
    {
        $taskKeys = RedisConnection::getKeys(KeyType::Task, $taskId);

        $tasks = [];
        foreach ($taskKeys as $taskKey) {
            $tasks[] = RedisConnection::getKey($taskKey);
        }

        return $tasks;
    }

    /**
     * @throws Exception
     */
    public static function createEntity(KeyType $type, array $data): void
    {
        $id = RedisConnection::incrementCounter($type);
        $data['id'] = $id;

        $key = "$type->value:$id";
        $result = RedisConnection::setKey($key, $data, 30 * 24 * 60 * 60);

        if (!$result) {
            throw new Exception("Failed to save $type->name to Redis");
        }
    }

    public static function updateEntity(KeyType $type, array $data, int $id, ?int $ttl = null): bool
    {
        $key = "$type->value:$id";

        if (!RedisConnection::keyExists($key)) {
            throw new NotFoundHttpException("$type->name not found");
        }

        $entity = RedisConnection::getKey($key);

        $updatedEntity = [
            ...$entity,
            ...$data
        ];

        return RedisConnection::setKey($key, $updatedEntity, $ttl);
    }

    public static function deleteEntity(KeyType $type, int $id): bool
    {
        $key = "$type->value:$id";
        return RedisConnection::delKey($key);
    }
}
