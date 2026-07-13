<?php

use App\Models\Todo;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $title = '';
    public string $description = '';

    public ?int $editingId = null;
    public string $editingTitle = '';

    /**
     * Create a new todo for the current user.
     */
    public function create(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Auth::user()->todos()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
        ]);

        $this->reset('title', 'description');
    }

    /**
     * Toggle a todo between completed and pending.
     */
    public function toggle(int $todoId): void
    {
        $todo = Auth::user()->todos()->findOrFail($todoId);

        $todo->update(['is_completed' => ! $todo->is_completed]);
    }

    /**
     * Start editing a todo title inline.
     */
    public function edit(int $todoId): void
    {
        $todo = Auth::user()->todos()->findOrFail($todoId);

        $this->editingId = $todo->id;
        $this->editingTitle = $todo->title;
        $this->resetValidation('editingTitle');
    }

    /**
     * Persist the inline title edit.
     */
    public function update(): void
    {
        $validated = $this->validate([
            'editingTitle' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->todos()->findOrFail($this->editingId)->update([
            'title' => $validated['editingTitle'],
        ]);

        $this->cancelEdit();
    }

    /**
     * Cancel the inline title edit.
     */
    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editingTitle');
        $this->resetValidation('editingTitle');
    }

    /**
     * Delete a todo.
     */
    public function delete(int $todoId): void
    {
        Auth::user()->todos()->findOrFail($todoId)->delete();

        if ($this->editingId === $todoId) {
            $this->cancelEdit();
        }
    }

    public function with(): array
    {
        return [
            'todos' => Auth::user()->todos()->latest('id')->get(),
        ];
    }
}; ?>

<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Todos') }}
            </h2>
        </div>
    </header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Create Todo -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form wire:submit="create" class="p-6 space-y-4">
                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input wire:model="title" id="title" name="title" type="text" class="mt-1 block w-full" required placeholder="{{ __('What needs to be done?') }}" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description (optional)')" />
                        <textarea wire:model="description" id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Add Todo') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <!-- Todo List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @forelse ($todos as $todo)
                        <div class="flex items-start gap-4 py-4 {{ $loop->last ? '' : 'border-b border-gray-100' }}" wire:key="todo-{{ $todo->id }}">
                            <input
                                type="checkbox"
                                wire:click="toggle({{ $todo->id }})"
                                @checked($todo->is_completed)
                                class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />

                            <div class="flex-1 min-w-0">
                                @if ($editingId === $todo->id)
                                    <form wire:submit="update" class="flex items-center gap-2">
                                        <x-text-input wire:model="editingTitle" type="text" class="block w-full text-sm" autofocus />
                                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                                        <x-secondary-button wire:click="cancelEdit" type="button">{{ __('Cancel') }}</x-secondary-button>
                                    </form>
                                    <x-input-error :messages="$errors->get('editingTitle')" class="mt-2" />
                                @else
                                    <p class="font-medium {{ $todo->is_completed ? 'line-through text-gray-400' : 'text-gray-900' }}">
                                        {{ $todo->title }}
                                    </p>
                                    @if ($todo->description)
                                        <p class="mt-1 text-sm text-gray-500">{{ $todo->description }}</p>
                                    @endif
                                @endif
                            </div>

                            @if ($editingId !== $todo->id)
                                <div class="flex items-center gap-3 text-sm">
                                    <button wire:click="edit({{ $todo->id }})" class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('Edit') }}
                                    </button>
                                    <button wire:click="delete({{ $todo->id }})" wire:confirm="{{ __('Delete this todo?') }}" class="text-red-600 hover:text-red-900">
                                        {{ __('Delete') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500">{{ __('No todos yet. Add your first one above.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
