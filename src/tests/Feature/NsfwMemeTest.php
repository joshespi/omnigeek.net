<?php

namespace Tests\Feature;

use App\Livewire\ComposePost;
use App\Livewire\MemeFeed;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NsfwMemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_meme_can_be_marked_nsfw(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.body', 'spicy meme')
            ->set('form.toMemes', true)
            ->set('form.nsfw', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Post::anyFeed()->first()->nsfw);
    }

    public function test_a_main_feed_post_is_never_nsfw(): void
    {
        $user = User::factory()->create();

        // NSFW only applies to memes — ticking it without toMemes must not persist.
        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.body', 'normal post')
            ->set('form.toMemes', false)
            ->set('form.nsfw', true)
            ->call('save');

        $this->assertFalse(Post::first()->nsfw);
    }

    public function test_nsfw_memes_render_a_reveal_gate(): void
    {
        // The blur gate lives on the image gallery, so the meme needs media.
        $post = Post::factory()->nsfw()->create(['body' => 'hidden meme']);
        $post->media()->create(['path' => 'uploads/x.jpg', 'type' => 'image', 'sort_order' => 0]);

        Livewire::test(MemeFeed::class)
            ->assertSee('Click to reveal');
    }

    public function test_safe_memes_do_not_render_a_reveal_gate(): void
    {
        $post = Post::factory()->memes()->create(['body' => 'safe meme']);
        $post->media()->create(['path' => 'uploads/y.jpg', 'type' => 'image', 'sort_order' => 0]);

        Livewire::test(MemeFeed::class)
            ->assertDontSee('Click to reveal');
    }

    public function test_the_memes_page_shows_a_content_warning_gate(): void
    {
        Livewire::test(MemeFeed::class)
            ->assertSee('Content warning')
            ->assertSee('Enter');
    }
}
