<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TodosTest extends TestCase
{
    use RefreshDatabase;

    public function test_todos_page_requires_authentication(): void
    {
        $response = $this->get('/todos');

        $response->assertRedirect('/login');
    }

    public function test_todos_page_is_displayed(): void
    {
        $user = User::factory()->create();

        Todo::create([
            'user_id' => $user->id,
            'title' => 'Write feature tests',
            'description' => 'Cover the todos page',
        ]);

        $response = $this->actingAs($user)->get('/todos');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.todos')
            ->assertSee('Write feature tests')
            ->assertSee('Cover the todos page');
    }

    public function test_user_can_create_a_todo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('pages.todos')
            ->set('title', 'New todo')
            ->set('description', 'Some details')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => 'New todo',
            'description' => 'Some details',
            'is_completed' => false,
        ]);
    }

    public function test_user_can_toggle_a_todo(): void
    {
        $user = User::factory()->create();

        $todo = Todo::create(['user_id' => $user->id, 'title' => 'Toggle me']);

        $this->actingAs($user);

        Volt::test('pages.todos')
            ->call('toggle', $todo->id)
            ->assertHasNoErrors();

        $this->assertTrue($todo->refresh()->is_completed);
    }

    public function test_user_can_edit_a_todo_title_inline(): void
    {
        $user = User::factory()->create();

        $todo = Todo::create(['user_id' => $user->id, 'title' => 'Old title']);

        $this->actingAs($user);

        Volt::test('pages.todos')
            ->call('edit', $todo->id)
            ->set('editingTitle', 'New title')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertSame('New title', $todo->refresh()->title);
    }

    public function test_user_can_delete_a_todo(): void
    {
        $user = User::factory()->create();

        $todo = Todo::create(['user_id' => $user->id, 'title' => 'Delete me']);

        $this->actingAs($user);

        Volt::test('pages.todos')
            ->call('delete', $todo->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }
}
