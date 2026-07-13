<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_page_requires_authentication(): void
    {
        $response = $this->get('/users');

        $response->assertRedirect('/login');
    }

    public function test_users_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->get('/users');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.users')
            ->assertSee($user->username)
            ->assertSee($other->username)
            ->assertSee($other->email);
    }

    public function test_user_can_follow_and_unfollow_another_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user);

        Volt::test('pages.users')
            ->call('toggleFollow', $other->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('follows', [
            'follower_id' => $user->id,
            'following_id' => $other->id,
        ]);

        Volt::test('pages.users')
            ->call('toggleFollow', $other->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $user->id,
            'following_id' => $other->id,
        ]);
    }
}
