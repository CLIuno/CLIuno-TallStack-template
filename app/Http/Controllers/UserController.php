<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getCurrent(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        return response()->json(['data' => ['user' => $user]]);
    }

    public function updateCurrent(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,'.$user->id],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'date_of_birth' => ['sometimes', 'nullable', 'string'],
            'gender' => ['sometimes', 'nullable', 'string'],
            'nationality' => ['sometimes', 'nullable', 'string'],
            'phone' => ['sometimes', 'nullable', 'string'],
        ]);

        $user->update($validated);

        return response()->json(['data' => ['user' => $user->fresh()->load('role')]]);
    }

    public function deleteCurrent(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }

    public function getById(int $id): JsonResponse
    {
        $user = User::with('role')->findOrFail($id);

        return response()->json(['data' => ['user' => $user]]);
    }

    public function getByUsername(string $username): JsonResponse
    {
        $user = User::with('role')->where('username', $username)->firstOrFail();

        return response()->json(['data' => ['user' => $user]]);
    }

    public function getAll(): JsonResponse
    {
        $users = User::with('role')->latest()->get();

        return response()->json(['data' => ['users' => $users]]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,'.$user->id],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'unique:users,email,'.$user->id],
            'role_id' => ['sometimes', 'nullable', 'exists:roles,id'],
        ]);

        $user->update($validated);

        return response()->json(['data' => ['user' => $user->fresh()->load('role')]]);
    }

    public function delete(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function getPostsByUserId(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $posts = $user->posts()->with(['user', 'comments.user'])->latest()->get();

        return response()->json(['data' => ['posts' => $posts]]);
    }

    public function getRolesByUserId(int $id): JsonResponse
    {
        $role = User::findOrFail($id)->role;

        return response()->json(['data' => ['role' => $role]]);
    }
}
