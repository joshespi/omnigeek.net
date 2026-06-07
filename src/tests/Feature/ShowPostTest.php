<?php

namespace Tests\Feature;

use App\Enums\Feed;
use App\Livewire\ShowPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_view_a_single_post(): void
    {
        $post = Post::factory()->create(['body' => 'A shareable post']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('A shareable post')
            ->assertSee('Copy link to this post');
    }

    public function test_an_author_can_delete_from_the_post_page(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(ShowPost::class, ['post' => $post])
            ->call('deletePost', $post)
            ->assertRedirect(route('home'));

        $this->assertModelMissing($post);
    }

    public function test_a_non_author_cannot_delete_from_the_post_page(): void
    {
        $post = Post::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ShowPost::class, ['post' => $post])
            ->call('deletePost', $post)
            ->assertForbidden();

        $this->assertModelExists($post);
    }

    public function test_an_author_can_move_a_main_post_to_memes(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(ShowPost::class, ['post' => $post])
            ->call('moveToOtherFeed', $post);

        $this->assertSame(Feed::Memes, $post->refresh()->feed);
    }

    public function test_moving_a_meme_sends_it_back_to_the_main_feed(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->memes()->create();

        Livewire::actingAs($user)
            ->test(ShowPost::class, ['post' => $post])
            ->call('moveToOtherFeed', $post);

        $this->assertSame(Feed::Main, $post->refresh()->feed);
    }

    public function test_an_admin_can_move_someone_elses_post(): void
    {
        $post = Post::factory()->create();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(ShowPost::class, ['post' => $post])
            ->call('moveToOtherFeed', $post);

        $this->assertSame(Feed::Memes, $post->refresh()->feed);
    }

    public function test_a_stranger_cannot_move_a_post(): void
    {
        $post = Post::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ShowPost::class, ['post' => $post])
            ->call('moveToOtherFeed', $post)
            ->assertForbidden();

        $this->assertSame(Feed::Main, $post->refresh()->feed);
    }
}
