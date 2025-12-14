<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class TaskController extends Controller
{
    public function index()
    {
        try {
            $redis = Redis::connection()->client();
            $keys = $redis->keys('task:*');
            $keys = array_filter($keys, fn($k) => !str_contains($k, 'counter'));

            $tasks = [];
            foreach ($keys as $key) {
                try {
                    $taskJson = $redis->rawCommand('JSON.GET', $key);
                    if ($taskJson) {
                        $tasks[] = json_decode($taskJson, true);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            usort($tasks, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
            return response()->json($tasks);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'due_date' => 'nullable|date'
        ]);

        try {
            $redis = Redis::connection()->client();
            $taskId = $redis->incr('task:counter');
            $key = "task:{$taskId}";

            $task = [
                'id' => (int)$taskId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'completed' => false,
                'created_at' => now()->toISOString(),
                'due_date' => $validated['due_date'] ?? null
            ];

            $jsonString = json_encode($task);
            $result = $redis->rawCommand('JSON.SET', $key, '$', $jsonString);

            if (!$result) {
                throw new \Exception('Failed to save task to Redis');
            }

            $redis->expire($key, 2592000);

            return response()->json($task, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Store error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggle($id)
    {
        try {
            $redis = Redis::connection()->client();
            $key = "task:{$id}";

            if (!$redis->exists($key)) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $taskJson = $redis->rawCommand('JSON.GET', $key);
            $task = json_decode($taskJson, true);
            $task['completed'] = !$task['completed'];

            $redis->rawCommand('JSON.SET', $key, '$', json_encode($task));

            return response()->json($task);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $redis = Redis::connection()->client();
            $key = "task:{$id}";

            if (!$redis->exists($key)) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $redis->del($key);
            return response()->json(['message' => 'Task deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
