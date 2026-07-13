<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Request $request, int $userId): JsonResponse
    {
        $targetUser = User::findOrFail($userId);

        if ($request->user()->id === $targetUser->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 422);
        }

        $existing = Follow::where('follower_id', $request->user()->id)
            ->where('following_id', $targetUser->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already following this user'], 409);
        }

        Follow::create([
            'follower_id' => $request->user()->id,
            'following_id' => $targetUser->id,
        ]);

        return response()->json(['message' => 'Followed successfully'], 201);
    }

    public function unfollow(Request $request, int $userId): JsonResponse
    {
        $follow = Follow::where('follower_id', $request->user()->id)
            ->where('following_id', $userId)
            ->firstOrFail();

        $follow->delete();

        return response()->json(['message' => 'Unfollowed successfully']);
    }

    public function getFollowers(int $userId): JsonResponse
    {
        User::findOrFail($userId);
        $followers = Follow::with('follower')
            ->where('following_id', $userId)
            ->get()
            ->pluck('follower');

        return response()->json(['data' => ['followers' => $followers]]);
    }

    public function getFollowing(int $userId): JsonResponse
    {
        User::findOrFail($userId);
        $following = Follow::with('following')
            ->where('follower_id', $userId)
            ->get()
            ->pluck('following');

        return response()->json(['data' => ['following' => $following]]);
    }

    public function isFollowing(Request $request, int $userId): JsonResponse
    {
        $isFollowing = Follow::where('follower_id', $request->user()->id)
            ->where('following_id', $userId)
            ->exists();

        return response()->json(['data' => ['isFollowing' => $isFollowing]]);
    }
}
