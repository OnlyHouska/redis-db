<?php

namespace App\Http\Controllers\Api;

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

    public function index(Request $request): JsonResponse
    {
        try {
            $tasks = $this->service->getTasks($request->get('user_id'));
            usort($tasks, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
            return response()->json($tasks);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'due_date' => 'nullable|date'
        ]);

        try {
            $task = [
                'title'         => $validated['title'],
                'description'   => $validated['description'],
                'category'      => $validated['category'],
                'due_date'      => $validated['due_date'] ?? null,
                'completed'     => false,
                'created_at'    => now()->toISOString(),
            ];

            $this->service->createEntity(KeyType::Task, $task, $request->get('user_id'));

            return response()->json($task, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggle(Request $request, $id): JsonResponse
    {
        try {
            $key = KeyType::Task->value . ":{$id}";
            $task = RedisConnection::getKey($key);

            $updateObject = [
                'completed' => !$task['completed'],
            ];

            $this->service->updateEntity(KeyType::Task, $updateObject, $id, $request->get('user_id'));

            return response()->json(RedisConnection::getKey($key), 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->service->deleteEntity(KeyType::Task, $id, $request->get('user_id'));
            return response()->json(['message' => 'Task deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
