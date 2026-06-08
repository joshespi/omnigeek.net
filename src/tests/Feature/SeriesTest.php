<?php

namespace Tests\Feature;

use App\Livewire\ComposePost;
use App\Livewire\SeriesFeed;
use App\Livewire\ShowPost;
use App\Models\Post;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SeriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_composing_with_a_series_name_creates_and_attaches_it(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ComposePost::class)
            ->set('form.body', 'Part one of my deep dive')
            ->set('form.series', 'Rust Deep Dive')
            ->set('form.seriesPart', 1)
            ->call('save');

        $post = Post::firstOrFail();

        $this->assertSame('Rust Deep Dive', $post->series->name);
        $this->assertSame(1, $post->series_part);
        $this->assertSame('rust-deep-dive', $post->series->slug);
    }

    public function test_two_authors_using_the_same_name_share_one_series(): void
    {
        foreach (['Alice', 'Bob'] as $name) {
            Livewire::actingAs(User::factory()->create(['name' => $name]))
                ->test(ComposePost::class)
                ->set('form.body', "Post by {$name}")
                ->set('form.series', 'Homelab Saga')
                ->call('save');
        }

        $this->assertSame(1, Series::count());
        $this->assertSame(2, Series::first()->posts()->count());
    }

    public function test_a_meme_post_never_gets_a_series(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ComposePost::class)
            ->set('form.body', 'a meme with a series name typed')
            ->set('form.series', 'Should Be Ignored')
            ->set('form.seriesPart', 3)
            ->set('form.toMemes', true)
            ->call('save');

        $post = Post::anyFeed()->firstOrFail();

        $this->assertNull($post->series_id);
        $this->assertNull($post->series_part);
        $this->assertSame(0, Series::count());
    }

    public function test_series_page_lists_published_posts_in_part_order(): void
    {
        $series = Series::factory()->create();
        Post::factory()->inSeries($series, 2)->create(['title' => 'Second']);
        Post::factory()->inSeries($series, 1)->create(['title' => 'First']);
        Post::factory()->inSeries($series, 3)->create([
            'title' => 'Unpublished',
            'published_at' => now()->addWeek(),
        ]);

        $html = $this->get(route('series.show', $series))
            ->assertOk()
            ->assertSee('First')
            ->assertSee('Second')
            ->assertDontSee('Unpublished')
            ->getContent();

        $this->assertLessThan(strpos($html, 'Second'), strpos($html, 'First'));
    }

    public function test_single_post_shows_its_position_and_neighbours(): void
    {
        $series = Series::factory()->create();
        $first = Post::factory()->inSeries($series, 1)->create(['title' => 'Intro']);
        $second = Post::factory()->inSeries($series, 2)->create(['title' => 'Middle']);
        $third = Post::factory()->inSeries($series, 3)->create(['title' => 'Finale']);

        Livewire::test(ShowPost::class, ['post' => $second])
            ->assertViewHas('series', function ($ctx) use ($first, $third) {
                return $ctx['position'] === 2
                    && $ctx['total'] === 3
                    && $ctx['prev']->is($first)
                    && $ctx['next']->is($third);
            })
            ->assertSee('Part 2 of 3')
            ->assertSee('Intro')   // prev label
            ->assertSee('Finale'); // next label
    }

    public function test_an_unpublished_post_has_no_series_context(): void
    {
        $series = Series::factory()->create();
        Post::factory()->inSeries($series, 1)->create();
        $scheduled = Post::factory()->inSeries($series, 2)->create([
            'published_at' => now()->addWeek(),
        ]);

        Livewire::test(ShowPost::class, ['post' => $scheduled])
            ->assertViewHas('series', null);
    }

    public function test_moving_a_series_post_to_memes_clears_its_series(): void
    {
        $series = Series::factory()->create();
        $post = Post::factory()->inSeries($series, 2)->create();

        // A direct feed flip (as moveToOtherFeed does) must not leave a meme in a series —
        // the invariant is enforced in the model, not just the compose form.
        $post->update(['feed' => \App\Enums\Feed::Memes]);

        $this->assertNull($post->refresh()->series_id);
        $this->assertNull($post->series_part);
    }

    public function test_a_part_number_without_a_series_is_dropped(): void
    {
        $post = Post::factory()->create(['series_part' => 7]);

        $this->assertNull($post->refresh()->series_part);
    }

    public function test_editing_a_post_can_change_its_series(): void
    {
        $user = User::factory()->create();
        $series = Series::factory()->create(['name' => 'Old Series']);
        $post = Post::factory()->for($user)->inSeries($series, 1)->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\PostEditor::class, ['post' => $post])
            ->call('startEditing')
            ->set('form.series', 'New Series')
            ->set('form.seriesPart', 5)
            ->call('save');

        $post->refresh();

        $this->assertSame('New Series', $post->series->name);
        $this->assertSame(5, $post->series_part);
    }
}
