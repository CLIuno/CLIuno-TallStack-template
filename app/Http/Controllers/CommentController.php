<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        $comments = $post->comments()->with('user')->latest()->get();

        return response()->json(['data' => ['comments' => $comments]]);
    }

    public function store(Request $request, int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $comment = $post->comments()->create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['data' => ['comment' => $comment->load('user')]], 201);
    }

    public function update(Request $request, int $postId, int $id): JsonResponse
    {
        Post::findOrFail($postId);
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $comment->update($validated);

        return response()->json(['data' => ['comment' => $comment->fresh()->load('user')]]);
    }

    public function destroy(Request $request, int $postId, int $id): JsonResponse
    {
        Post::findOrFail($postId);
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
