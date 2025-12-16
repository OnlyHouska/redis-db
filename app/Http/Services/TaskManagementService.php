<?php

namespace App\Http\Services;

use App\Auth\AuthContext;
use App\Constants\KeyType;
use App\Http\RedisConnection;
use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service for managing task entities with authorization
 * 
 * Handles CRUD operations for tasks with automatic user authorization checks.
 * All operations are scoped to the authenticated user and logged to Redis streams.
 */
readonly class TaskManagementService
{
    /**
     * @param AuthContext $auth Current authenticated user context
     */
    public function __construct(
        private AuthContext $auth
    ) {}

    /**
     * Get all tasks for the authenticated user
     * 
     * Optionally filters to a specific task ID
     * 
     * @param int|null $taskId Optional specific task ID to retrieve
     * @return array List of tasks belonging to the current user
     */
    public function getTasks(?int $taskId = null): array
    {
        // Get all task keys matching the pattern
        $taskKeys = RedisConnection::getKeys(KeyType::Task, $taskId);

        $tasks = [];
        foreach ($taskKeys as $taskKey) {
            // Retrieve task data
            $task = RedisConnection::getKey($taskKey);

            // Only include tasks belonging to the current user
            if (($task['user_id'] ?? null) === $this->auth->getUser()) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    /**
     * Create a new entity (task) in Redis
     * 
     * Automatically assigns a unique ID, associates with current user,
     * and logs creation event to Redis stream
     * 
     * @param KeyType $type Entity type (e.g., Task)
     * @param array $data Entity data
     * @return array Created entity with ID and user_id
     * @throws Exception If Redis operation fails
     */
    public function createEntity(KeyType $type, array $data): array
    {
        // Generate unique ID using Redis counter
        $id = RedisConnection::incrementCounter($type);

        // Add ID and user ownership
        $data['id'] = $id;
        $data['user_id'] = $this->auth->getUser();

        // Store in Redis with 30-day TTL
        $key = "$type->value:$id";
        $result = RedisConnection::setKey($key, $data, 30 * 24 * 60 * 60);

        if (!$result) {
            throw new Exception("Failed to save $type->name to Redis");
        }

        // Log creation event to Redis stream for audit/analytics
        RedisConnection::addToStream($type, [
            'event'             => "{$type->value}_created",
            "{$type->value}_id" => $id,
            'user_id'           => $this->auth->getUser(),
            'timestamp'         => now()->toISOString(),
        ]);

        return $data;
    }

    /**
     * Update an existing entity
     * 
     * Verifies entity exists and user has permission before updating.
     * Logs update event to Redis stream.
     * 
     * @param KeyType $type Entity type
     * @param array $data Fields to update
     * @param int $id Entity ID
     * @param int|null $ttl Optional TTL in seconds
     * @return bool True if update succeeded
     * @throws NotFoundHttpException If entity doesn't exist
     * @throws AccessDeniedHttpException If user doesn't own the entity
     */
    public function updateEntity(KeyType $type, array $data, int $id, ?int $ttl = null): bool
    {
        $key = "$type->value:$id";

        // Verify entity exists
        if (!RedisConnection::keyExists($key)) {
            throw new NotFoundHttpException("$type->name not found");
        }

        // Get current entity data
        $entity = RedisConnection::getKey($key);

        // Verify user owns this entity
        if (($entity['user_id'] ?? null) != $this->auth->getUser()) {
            throw new AccessDeniedHttpException("You are not allowed to edit this task");
        }

        // Merge updates with existing data (preserve user_id)
        $updatedEntity = [
            ...$entity,
            ...$data,
            'user_id' => $entity['user_id'],
        ];

        // Save updated entity to Redis
        $result = RedisConnection::setKey($key, $updatedEntity, $ttl);

        // Log update event with changed fields
        RedisConnection::addToStream($type, [
            'event'             => "{$type->value}_updated",
            "{$type->value}_id" => $id,
            'user_id'           => $this->auth->getUser(),
            'changes'           => json_encode(array_keys($data)),
            'timestamp'         => now()->toISOString(),
        ]);

        return $result;
    }

    /**
     * Delete an entity
     * 
     * Verifies entity exists and user has permission before deleting.
     * Logs deletion event to Redis stream.
     * 
     * @param KeyType $type Entity type
     * @param int $id Entity ID
     * @return bool True if deletion succeeded
     * @throws NotFoundHttpException If entity doesn't exist
     * @throws AccessDeniedHttpException If user doesn't own the entity
     */
    public function deleteEntity(KeyType $type, int $id): bool
    {
        $key = "$type->value:$id";

        // Verify entity exists
        if (!RedisConnection::keyExists($key)) {
            throw new NotFoundHttpException("$type->name not found");
        }

        // Get entity data to check ownership
        $entity = RedisConnection::getKey($key);

        // Verify user owns this entity
        if (($entity['user_id'] ?? null) != $this->auth->getUser()) {
            throw new AccessDeniedHttpException("You are not allowed to edit this $type->name");
        }

        // Delete from Redis
        $deleted = RedisConnection::delKey($key);

        // Log deletion event
        RedisConnection::addToStream($type, [
            'event'             => "{$type->value}_deleted",
            "{$type->value}_id" => $id,
            'user_id'           => $this->auth->getUser(),
            'timestamp'         => now()->toISOString(),
        ]);

        return $deleted;
    }
}
