<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $user = auth()->user();
        $todoCount = $user->todos()->count();
        $completedTodoCount = $user->todos()->where('is_completed', true)->count();
        $postCount = $user->posts()->count();
        $followerCount = $user->followers()->count();
        $followingCount = $user->following()->count();
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __('Welcome back, :name!', ['name' => $user->first_name ?: $user->username]) }}
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <a href="{{ route('todos') }}" wire:navigate class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('Todos') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $todoCount }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ __(':count completed', ['count' => $completedTodoCount]) }}</p>
                    </div>
                </a>

                <a href="{{ route('posts.index') }}" wire:navigate class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('Posts') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $postCount }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ __('written by you') }}</p>
                    </div>
                </a>

                <a href="{{ route('users.index') }}" wire:navigate class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('Followers') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $followerCount }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ __('following :count', ['count' => $followingCount]) }}</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
