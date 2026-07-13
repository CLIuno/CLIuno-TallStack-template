<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Volt::route('todos', 'pages.todos')
        ->name('todos');

    Volt::route('posts', 'pages.posts.index')
        ->name('posts.index');

    Volt::route('posts/{post}', 'pages.posts.show')
        ->name('posts.show');

    Volt::route('users', 'pages.users')
        ->name('users.index');
});

require __DIR__.'/auth.php';
