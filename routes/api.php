<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Token-based auth (the shared frontend contract)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/check-token', [AuthController::class, 'checkToken']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/send-verify-email', [AuthController::class, 'sendVerifyEmail']);
        Route::post('/otp/generate', [AuthController::class, 'otpGenerate']);
        Route::post('/otp/verify', [AuthController::class, 'otpVerify']);
        Route::post('/otp/validate', [AuthController::class, 'otpValidate']);
        Route::post('/otp/disable', [AuthController::class, 'otpDisable']);
    });
});

Route::middleware(['auth:sanctum'])->group(function () {

    // Users
    Route::get('/users', [UserController::class, 'getAll']);
    Route::get('/users/current', [UserController::class, 'getCurrent']);
    Route::patch('/users/current', [UserController::class, 'updateCurrent']);
    Route::delete('/users/current', [UserController::class, 'deleteCurrent']);
    Route::get('/users/username/{username}', [UserController::class, 'getByUsername']);
    Route::get('/users/{id}/posts', [UserController::class, 'getPostsByUserId']);
    Route::get('/users/{id}/roles', [UserController::class, 'getRolesByUserId']);
    Route::get('/users/{id}', [UserController::class, 'getById']);

    // Admin-only user routes
    Route::middleware('admin')->group(function () {
        Route::patch('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'delete']);
    });

    // Todos
    Route::get('/todos/current-user', [TodoController::class, 'currentUser']);
    Route::get('/todos', [TodoController::class, 'index']);
    Route::get('/todos/{id}', [TodoController::class, 'show']);
    Route::post('/todos', [TodoController::class, 'store']);
    Route::patch('/todos/{id}', [TodoController::class, 'update']);
    Route::delete('/todos/{id}', [TodoController::class, 'destroy']);
    Route::patch('/todos/{id}/toggle', [TodoController::class, 'toggleComplete']);

    // Posts
    Route::get('/posts/current-user', [PostController::class, 'currentUser']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::patch('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::get('/posts/{id}/user', [PostController::class, 'getUserByPostId']);

    // Comments on Posts
    Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::patch('/posts/{postId}/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/posts/{postId}/comments/{id}', [CommentController::class, 'destroy']);

    // Follows
    Route::post('/follows/{userId}/follow', [FollowController::class, 'follow']);
    Route::delete('/follows/{userId}/follow', [FollowController::class, 'unfollow']);
    Route::get('/follows/{userId}/followers', [FollowController::class, 'getFollowers']);
    Route::get('/follows/{userId}/following', [FollowController::class, 'getFollowing']);
    Route::get('/follows/{userId}/is-following', [FollowController::class, 'isFollowing']);

    // Roles (admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{id}', [RoleController::class, 'show']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::patch('/roles/{id}', [RoleController::class, 'update']);
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
        Route::get('/roles/{id}/users', [RoleController::class, 'getUsersByRoleId']);
    });
});
