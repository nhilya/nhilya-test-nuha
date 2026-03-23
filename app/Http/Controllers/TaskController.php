<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function index(): JsonResponse
    {
        $tasks = auth()->user()->tasks;

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ], Response::HTTP_OK);

    }

    public function store(TaskRequest $taskRequest): JsonResponse
    {
        $task = auth()->user()->tasks()->create($taskRequest->validated());

        return response()->json([
            'success' => true,
            'task' => $task,
        ], Response::HTTP_CREATED);
    }

    public function update(TaskRequest $taskRequest, int $id): JsonResponse
    {
        $task = auth()->user()->tasks()->findOrFail($id);
        $task->update($taskRequest->validated());

        return response()->json([
            'success' => true,
            'task' => $task,
        ], Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $task = auth()->user()->tasks()->findOrFail($id);
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ], Response::HTTP_OK);
    }
}
