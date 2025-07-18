<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/tasks
     */
    public function index()
    {
        return Task::all();
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/tasks
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task = Task::create($validated);
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     * GET /api/tasks/{id}
     */
    public function show(string $id)
    {
        return Task::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/tasks/{id}
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
        ]);

        $task = Task::findOrFail($id);
        $task->update($request->all());

        return response()->json($task, 200);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/tasks/{id}
     */
    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 204);
    }
}
