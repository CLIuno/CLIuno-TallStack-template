<?php

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Post $post;

    public string $newComment = '';

    public bool $editing = false;
    public string $title = '';
    public string $content = '';

    /**
     * Mount the component for the given post.
     */
    public function mount(Post $post): void
    {
        $this->post = $post;
    }

    /**
     * Add a comment to the post as the current user.
     */
    public function addComment(): void
    {
        $validated = $this->validate([
            'newComment' => ['required', 'string'],
        ]);

        $this->post->comments()->create([
            'content' => $validated['newComment'],
            'user_id' => Auth::id(),
        ]);

        $this->reset('newComment');
    }

    /**
     * Delete one of the current user's own comments.
     */
    public function deleteComment(int $commentId): void
    {
        $comment = $this->post->comments()->findOrFail($commentId);

        abort_unless($comment->user_id === Auth::id(), 403);

        $comment->delete();
    }

    /**
     * Start editing the post (owner only).
     */
    public function edit(): void
    {
        abort_unless($this->post->user_id === Auth::id(), 403);

        $this->title = $this->post->title;
        $this->content = $this->post->content;
        $this->editing = true;
    }

    /**
     * Persist edits to the post (owner only).
     */
    public function update(): void
    {
        abort_unless($this->post->user_id === Auth::id(), 403);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $this->post->update($validated);

        $this->editing = false;
    }

    /**
     * Cancel editing the post.
     */
    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->resetValidation();
    }

    /**
     * Delete the post (owner only).
     */
    public function deletePost(): void
    {
        abort_unless($this->post->user_id === Auth::id(), 403);

        $this->post->delete();

        $this->redirect(route('posts.index', absolute: false), navigate: true);
    }

    public function with(): array
    {
        return [
            'comments' => $this->post->comments()->with('user')->latest('id')->get(),
        ];
    }
}; ?>

<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Post') }}
            </h2>

            <a href="{{ route('posts.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-900">
                {{ __('Back to posts') }}
            </a>
        </div>
    </header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Post -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($editing)
                        <form wire:submit="update" class="space-y-4">
                            <div>
                                <x-input-label for="title" :value="__('Title')" />
                                <x-text-input wire:model="title" id="title" name="title" type="text" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="content" :value="__('Content')" />
                                <textarea wire:model="content" id="content" name="content" rows="5" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                <x-input-error :messages="$errors->get('content')" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end gap-3">
                                <x-secondary-button wire:click="cancelEdit" type="button">{{ __('Cancel') }}</x-secondary-button>
                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                            </div>
                        </form>
                    @else
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>{{ '@'.$post->user->username }}</span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                        </div>

                        <h3 class="mt-2 text-2xl font-semibold text-gray-900">{{ $post->title }}</h3>

                        <p class="mt-4 text-gray-700 whitespace-pre-line">{{ $post->content }}</p>

                        @if ($post->user_id === auth()->id())
                            <div class="mt-6 flex items-center gap-3 text-sm">
                                <button wire:click="edit" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('Edit') }}
                                </button>
                                <button wire:click="deletePost" wire:confirm="{{ __('Delete this post?') }}" class="text-red-600 hover:text-red-900">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Add Comment -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form wire:submit="addComment" class="p-6 space-y-4">
                    <div>
                        <x-input-label for="newComment" :value="__('Add a comment')" />
                        <textarea wire:model="newComment" id="newComment" name="newComment" rows="2" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                        <x-input-error :messages="$errors->get('newComment')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Comment') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <!-- Comments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="font-semibold text-gray-900">
                        {{ trans_choice(':count comment|:count comments', $comments->count(), ['count' => $comments->count()]) }}
                    </h4>

                    <div class="mt-4">
                        @forelse ($comments as $comment)
                            <div class="py-3 {{ $loop->last ? '' : 'border-b border-gray-100' }}" wire:key="comment-{{ $comment->id }}">
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <span>{{ '@'.$comment->user->username }} &middot; {{ $comment->created_at->diffForHumans() }}</span>

                                    @if ($comment->user_id === auth()->id())
                                        <button wire:click="deleteComment({{ $comment->id }})" wire:confirm="{{ __('Delete this comment?') }}" class="text-red-600 hover:text-red-900">
                                            {{ __('Delete') }}
                                        </button>
                                    @endif
                                </div>

                                <p class="mt-1 text-gray-700 whitespace-pre-line">{{ $comment->content }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500">{{ __('No comments yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
