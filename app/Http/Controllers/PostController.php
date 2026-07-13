<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::with(['user', 'comments.user'])->latest()->get();

        return response()->json(['data' => ['posts' => $posts]]);
    }

    public function show(int $id): JsonResponse
    {
        $post = Post::with(['user', 'comments.user'])->findOrFail($id);

        return response()->json(['data' => ['post' => $post]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image_url' => ['nullable', 'string', 'url'],
        ]);

        $post = $request->user()->posts()->create($validated);

        return response()->json(['data' => ['post' => $post->load(['user', 'comments.user'])]], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'image_url' => ['sometimes', 'nullable', 'string', 'url'],
        ]);

        $post->update($validated);

        return response()->json(['data' => ['post' => $post->fresh()->load(['user', 'comments.user'])]]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function currentUser(Request $request): JsonResponse
    {
        $posts = $request->user()->posts()->with(['user', 'comments.user'])->latest()->get();

        return response()->json(['data' => ['posts' => $posts]]);
    }

    public function getUserByPostId(int $id): JsonResponse
    {
        $post = Post::with('user')->findOrFail($id);

        return response()->json(['data' => ['user' => $post->user]]);
    }
}
