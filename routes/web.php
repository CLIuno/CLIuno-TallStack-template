<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Route::livewire('todos', 'pages.todos')
        ->name('todos');

    Route::livewire('posts', 'pages.posts.index')
        ->name('posts.index');

    Route::livewire('posts/{post}', 'pages.posts.show')
        ->name('posts.show');

    Route::livewire('users', 'pages.users')
        ->name('users.index');
});

require __DIR__.'/auth.php';
