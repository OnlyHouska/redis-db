<?php

namespace App\Http\Controllers\Api;

use App\Constants\Category;
use App\Constants\KeyType;
use App\Http\Controllers\Controller;
use App\Http\RedisConnection;
use App\Http\Services\TaskManagementService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskManagementService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $tasks = $this->service->getTasks();
            usort($tasks, fn($a, $b) => strtotime($b['created_at'] ?? '') <=> strtotime($a['created_at'] ?? ''));
            return response()->json($tasks);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'required|string',
            'due_date'    => 'nullable|date'
        ]);

        try {
            if (!in_array($validated['category'], Category::values(), true)) {
                return response()->json(['error' => 'Invalid category'], 401);
            }

            $task = [
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'category'    => $validated['category'],
                'due_date'    => $validated['due_date'] ?? null,
                'completed'   => false,
                'created_at'  => now()->toISOString(),
            ];

            $created = $this->service->createEntity(KeyType::Task, $task);

            return response()->json($created, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggle(int $id): JsonResponse
    {
        try {
            $key = KeyType::Task->value . ":{$id}";

            if (!RedisConnection::keyExists($key)) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $task = RedisConnection::getKey($key);

            $updateObject = [
                'completed' => !($task['completed'] ?? false),
            ];

            $this->service->updateEntity(KeyType::Task, $updateObject, $id);

            return response()->json(RedisConnection::getKey($key), 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteEntity(KeyType::Task, $id);
            return response()->json(['message' => 'Task deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
