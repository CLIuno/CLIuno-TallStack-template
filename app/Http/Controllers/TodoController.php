<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(): JsonResponse
    {
        $todos = Todo::with('user')->latest()->get();

        return response()->json(['data' => ['todos' => $todos]]);
    }

    public function show(int $id): JsonResponse
    {
        $todo = Todo::with('user')->findOrFail($id);

        return response()->json(['data' => ['todo' => $todo]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $todo = $request->user()->todos()->create($validated);

        return response()->json(['data' => ['todo' => $todo->load('user')]], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $todo = Todo::findOrFail($id);

        if ($todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $todo->update($validated);

        return response()->json(['data' => ['todo' => $todo->fresh()->load('user')]]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $todo = Todo::findOrFail($id);

        if ($todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $todo->delete();

        return response()->json(['message' => 'Todo deleted successfully']);
    }

    public function toggleComplete(Request $request, int $id): JsonResponse
    {
        $todo = Todo::findOrFail($id);

        if ($todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $todo->update(['is_completed' => ! $todo->is_completed]);

        return response()->json(['data' => ['todo' => $todo->fresh()->load('user')]]);
    }

    public function currentUser(Request $request): JsonResponse
    {
        $todos = $request->user()->todos()->with('user')->latest()->get();

        return response()->json(['data' => ['todos' => $todos]]);
    }
}
