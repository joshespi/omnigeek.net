<?php

namespace Tests\Feature;

use App\Enums\Feed as FeedEnum;
use App\Jobs\NotifySubscribersOfNewPost;
use App\Livewire\ComposePost;
use App\Livewire\Feed;
use App\Livewire\GeekProfile;
use App\Livewire\MemeFeed;
use App\Livewire\Search;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class MemeFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_compose_toggle_routes_a_post_to_the_memes_feed(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.body', 'dank meme')
            ->set('form.toMemes', true)
            ->call('save')
            ->assertHasNoErrors();

        // Global scope hides memes from a bare query, so look it up via anyFeed().
        $this->assertSame(FeedEnum::Memes, Post::anyFeed()->first()->feed);
    }

    public function test_posts_default_to_the_main_feed(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.body', 'real post')
            ->call('save');

        $this->assertSame(FeedEnum::Main, Post::first()->feed);
    }

    public function test_memes_are_hidden_from_the_main_feed(): void
    {
        Post::factory()->create(['body' => 'main content']);
        Post::factory()->memes()->create(['body' => 'meme content']);

        Livewire::test(Feed::class)
            ->assertSee('main content')
            ->assertDontSee('meme content');
    }

    public function test_the_memes_feed_shows_only_memes(): void
    {
        Post::factory()->create(['body' => 'main content']);
        Post::factory()->memes()->create(['body' => 'meme content']);

        Livewire::test(MemeFeed::class)
            ->assertSee('meme content')
            ->assertDontSee('main content');
    }

    public function test_the_author_can_delete_a_meme_from_the_memes_feed(): void
    {
        $user = User::factory()->create();
        $meme = Post::factory()->for($user)->memes()->create(['body' => 'junk']);

        Livewire::actingAs($user)
            ->test(MemeFeed::class)
            ->assertSee('Delete')
            ->call('deletePost', $meme->id);

        $this->assertModelMissing($meme);
    }

    public function test_a_stranger_sees_no_delete_button_on_the_memes_feed(): void
    {
        $owner = User::factory()->create();
        Post::factory()->for($owner)->memes()->create(['body' => 'junk']);

        Livewire::actingAs(User::factory()->create())
            ->test(MemeFeed::class)
            ->assertDontSee('Delete');
    }

    public function test_memes_are_hidden_from_search(): void
    {
        Post::factory()->memes()->create(['body' => 'searchable meme']);

        Livewire::test(Search::class)
            ->set('query', 'searchable')
            ->assertDontSee('searchable meme');
    }

    public function test_memes_are_hidden_from_the_geek_profile(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->create(['body' => 'profile main post']);
        Post::factory()->for($user)->memes()->create(['body' => 'profile meme post']);

        Livewire::test(GeekProfile::class, ['user' => $user])
            ->assertSee('profile main post')
            ->assertDontSee('profile meme post');
    }

    public function test_posting_a_meme_does_not_dispatch_the_notify_job(): void
    {
        Queue::fake();

        Livewire::actingAs(User::factory()->create())
            ->test(ComposePost::class)
            ->set('form.body', 'dank meme')
            ->set('form.toMemes', true)
            ->call('save');

        Queue::assertNotPushed(NotifySubscribersOfNewPost::class);
    }

    public function test_posting_to_the_main_feed_dispatches_the_notify_job(): void
    {
        Queue::fake();

        Livewire::actingAs(User::factory()->create())
            ->test(ComposePost::class)
            ->set('form.body', 'real post')
            ->call('save');

        Queue::assertPushed(NotifySubscribersOfNewPost::class);
    }
}
