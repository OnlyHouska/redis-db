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

/**
 * API Controller for managing tasks
 * 
 * Handles CRUD operations for tasks including listing, creating,
 * toggling completion status, and deleting tasks.
 */
class TaskController extends Controller
{
    /**
     * @param TaskManagementService $service Service for task business logic
     */
    public function __construct(
        private readonly TaskManagementService $service
    ) {}

    /**
     * Get all tasks for the authenticated user
     * 
     * Returns tasks sorted by creation date (newest first)
     * 
     * @return JsonResponse List of tasks or error message
     */
    public function index(): JsonResponse
    {
        try {
            // Fetch all tasks for the current user
            $tasks = $this->service->getTasks();
            
            // Sort tasks by creation date (newest first)
            usort($tasks, fn($a, $b) => strtotime($b['created_at'] ?? '') <=> strtotime($a['created_at'] ?? ''));
            
            return response()->json($tasks);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new task
     * 
     * @param Request $request HTTP request with task data
     * @return JsonResponse Created task or error message
     */
    public function store(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'required|string',
            'due_date'    => 'nullable|date'
        ]);

        try {
            // Verify that the category is valid
            if (!in_array($validated['category'], Category::values(), true)) {
                return response()->json(['error' => 'Invalid category'], 401);
            }

            // Prepare task data with defaults
            $task = [
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'category'    => $validated['category'],
                'due_date'    => $validated['due_date'] ?? null,
                'completed'   => false,
                'created_at'  => now()->toISOString(),
            ];

            // Create the task in Redis
            $created = $this->service->createEntity(KeyType::Task, $task);

            return response()->json($created, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle the completion status of a task
     * 
     * @param int $id Task ID
     * @return JsonResponse Updated task or error message
     */
    public function toggle(int $id): JsonResponse
    {
        try {
            // Build the Redis key for this task
            $key = KeyType::Task->value . ":{$id}";

            // Check if task exists
            if (!RedisConnection::keyExists($key)) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            // Get current task data
            $task = RedisConnection::getKey($key);

            // Prepare update with toggled completion status
            $updateObject = [
                'completed' => !($task['completed'] ?? false),
            ];

            // Update the task
            $this->service->updateEntity(KeyType::Task, $updateObject, $id);

            // Return the updated task
            return response()->json(RedisConnection::getKey($key), 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a task
     * 
     * @param int $id Task ID
     * @return JsonResponse Success message or error
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Delete the task (with authorization check inside service)
            $this->service->deleteEntity(KeyType::Task, $id);
            
            return response()->json(['message' => 'Task deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
