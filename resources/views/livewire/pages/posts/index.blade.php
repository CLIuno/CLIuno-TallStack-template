<?php

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $title = '';
    public string $content = '';

    /**
     * Create a new post for the current user.
     */
    public function create(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        Auth::user()->posts()->create($validated);

        $this->reset('title', 'content');
    }

    public function with(): array
    {
        return [
            'posts' => Post::with('user')->withCount('comments')->latest('id')->get(),
        ];
    }
}; ?>

<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Posts') }}
            </h2>
        </div>
    </header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Create Post -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form wire:submit="create" class="p-6 space-y-4">
                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input wire:model="title" id="title" name="title" type="text" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="content" :value="__('Content')" />
                        <textarea wire:model="content" id="content" name="content" rows="3" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                        <x-input-error :messages="$errors->get('content')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Publish Post') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <!-- Posts List -->
            @forelse ($posts as $post)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" wire:key="post-{{ $post->id }}">
                    <div class="p-6">
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>{{ '@'.$post->user->username }}</span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                        </div>

                        <a href="{{ route('posts.show', $post) }}" wire:navigate class="mt-2 block">
                            <h3 class="text-lg font-semibold text-gray-900 hover:text-indigo-600">
                                {{ $post->title }}
                            </h3>
                        </a>

                        <p class="mt-2 text-gray-700 whitespace-pre-line">{{ Str::limit($post->content, 200) }}</p>

                        <div class="mt-4">
                            <a href="{{ route('posts.show', $post) }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-900">
                                {{ trans_choice(':count comment|:count comments', $post->comments_count, ['count' => $post->comments_count]) }} &mdash; {{ __('View post') }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-500">
                        {{ __('No posts yet. Be the first to publish one.') }}
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
