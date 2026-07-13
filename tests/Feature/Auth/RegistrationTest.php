<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('username', 'testuser')
            ->set('first_name', 'Test')
            ->set('last_name', 'User')
            ->set('email', 'test@example.com')
            ->set('phone', '+15550100')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();

        $user = User::where('username', 'testuser')->firstOrFail();
        $this->assertSame('Test', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame('+15550100', $user->phone);
    }

    public function test_registration_assigns_the_default_user_role_on_an_empty_database(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('username', 'firstuser')
            ->set('first_name', 'First')
            ->set('last_name', 'User')
            ->set('email', 'first@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertHasNoErrors();

        $user = User::where('username', 'firstuser')->firstOrFail();
        $this->assertSame('user', $user->role->name);
        $this->assertNull($user->phone);
    }
}
