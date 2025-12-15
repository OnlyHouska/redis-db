<?php

namespace App\Http\Services;

use App\Constants\KeyType;
use App\Http\RedisConnection;
use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskManagementService
{
    public static function getTasks(int $userId, ?int $taskId = null): array
    {
        $taskKeys = RedisConnection::getKeys(KeyType::Task, $taskId);

        $tasks = [];
        foreach ($taskKeys as $taskKey) {
            $task = RedisConnection::getKey($taskKey);

            if (($task['user_id'] ?? null) === $userId) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    /**
     * @throws Exception
     */
    public static function createEntity(KeyType $type, array $data, int $userId): void
    {
        $id = RedisConnection::incrementCounter($type);
        $data['id'] = $id;
        $data['user_id'] = $userId;

        $key = "$type->value:$id";
        $result = RedisConnection::setKey($key, $data, 30 * 24 * 60 * 60);

        if (!$result) {
            throw new Exception("Failed to save $type->name to Redis");
        }
    }

    public static function updateEntity(KeyType $type, array $data, int $id, int $userId, ?int $ttl = null): bool
    {
        $key = "$type->value:$id";

        if (!RedisConnection::keyExists($key)) {
            throw new NotFoundHttpException("$type->name not found");
        }

        $entity = RedisConnection::getKey($key);

        if (($entity['user_id'] ?? null) != $userId) {
            throw new AccessDeniedHttpException("You are not allowed to edit this task");
        }

        $updatedEntity = [
            ...$entity,
            ...$data,
            'user_id' => $entity['user_id'],
        ];

        return RedisConnection::setKey($key, $updatedEntity, $ttl);
    }

    public static function deleteEntity(KeyType $type, int $id, int $userId): bool
    {
        $key = "$type->value:$id";

        if (!RedisConnection::keyExists($key)) {
            throw new NotFoundHttpException("$type->name not found");
        }

        $entity = RedisConnection::getKey($key);

        if (($entity['user_id'] ?? null) != $userId) {
            throw new AccessDeniedHttpException("You are not allowed to edit this $type->name");
        }

        return RedisConnection::delKey($key);
    }
}
