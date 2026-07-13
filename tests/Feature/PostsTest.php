<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_page_requires_authentication(): void
    {
        $response = $this->get('/posts');

        $response->assertRedirect('/login');
    }

    public function test_posts_page_is_displayed_with_author_username(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        Post::create([
            'user_id' => $author->id,
            'title' => 'Hello CLIuno',
            'content' => 'The very first post',
        ]);

        $response = $this->actingAs($user)->get('/posts');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.posts.index')
            ->assertSee('Hello CLIuno')
            ->assertSee($author->username);
    }

    public function test_post_detail_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Detailed post',
            'content' => 'Full content here',
        ]);

        Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'A fine comment',
        ]);

        $response = $this->actingAs($user)->get('/posts/'.$post->id);

        $response
            ->assertOk()
            ->assertSeeVolt('pages.posts.show')
            ->assertSee('Detailed post')
            ->assertSee('Full content here')
            ->assertSee('A fine comment');
    }

    public function test_user_can_create_a_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('pages.posts.index')
            ->set('title', 'A new post')
            ->set('content', 'Fresh content')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'A new post',
            'content' => 'Fresh content',
        ]);
    }

    public function test_user_can_comment_on_a_post(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $post = Post::create([
            'user_id' => $author->id,
            'title' => 'Comment target',
            'content' => 'Content',
        ]);

        $this->actingAs($user);

        Volt::test('pages.posts.show', ['post' => $post])
            ->set('newComment', 'Great write-up!')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Great write-up!',
        ]);
    }

    public function test_author_can_update_and_delete_their_post(): void
    {
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Original title',
            'content' => 'Original content',
        ]);

        $this->actingAs($user);

        Volt::test('pages.posts.show', ['post' => $post])
            ->call('edit')
            ->set('title', 'Updated title')
            ->set('content', 'Updated content')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertSame('Updated title', $post->refresh()->title);

        Volt::test('pages.posts.show', ['post' => $post])
            ->call('deletePost')
            ->assertRedirect(route('posts.index', absolute: false));

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
