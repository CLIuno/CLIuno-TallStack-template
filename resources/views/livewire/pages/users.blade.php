<?php

use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    /**
     * Follow or unfollow the given user.
     */
    public function toggleFollow(int $userId): void
    {
        abort_if($userId === Auth::id(), 403);

        $user = User::findOrFail($userId);

        $follow = Follow::where('follower_id', Auth::id())
            ->where('following_id', $user->id)
            ->first();

        if ($follow) {
            $follow->delete();

            return;
        }

        Follow::create([
            'follower_id' => Auth::id(),
            'following_id' => $user->id,
        ]);
    }

    public function with(): array
    {
        return [
            'users' => User::withCount('followers')->orderBy('username')->get(),
            'followingIds' => Follow::where('follower_id', Auth::id())
                ->pluck('following_id')
                ->all(),
        ];
    }
}; ?>

<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Users') }}
            </h2>
        </div>
    </header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @forelse ($users as $user)
                        <div class="flex items-center justify-between gap-4 py-4 {{ $loop->last ? '' : 'border-b border-gray-100' }}" wire:key="user-{{ $user->id }}">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">
                                    {{ '@'.$user->username }}
                                    @if ($user->id === auth()->id())
                                        <span class="ms-1 text-xs text-gray-400">({{ __('you') }})</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ __('Joined :date', ['date' => $user->created_at->format('M j, Y')]) }}
                                    &middot;
                                    {{ trans_choice(':count follower|:count followers', $user->followers_count, ['count' => $user->followers_count]) }}
                                </p>
                            </div>

                            @if ($user->id !== auth()->id())
                                @if (in_array($user->id, $followingIds))
                                    <x-secondary-button wire:click="toggleFollow({{ $user->id }})">
                                        {{ __('Unfollow') }}
                                    </x-secondary-button>
                                @else
                                    <x-primary-button wire:click="toggleFollow({{ $user->id }})">
                                        {{ __('Follow') }}
                                    </x-primary-button>
                                @endif
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500">{{ __('No users found.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
