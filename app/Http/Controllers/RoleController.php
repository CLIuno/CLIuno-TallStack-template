<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::latest()->get();

        return response()->json(['data' => ['roles' => $roles]]);
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        return response()->json(['data' => ['role' => $role]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
        ]);

        $role = Role::create($validated);

        return response()->json(['data' => ['role' => $role]], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
        ]);

        $role->update($validated);

        return response()->json(['data' => ['role' => $role->fresh()]]);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function getUsersByRoleId(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $users = $role->users()->get();

        return response()->json(['data' => ['users' => $users]]);
    }
}
