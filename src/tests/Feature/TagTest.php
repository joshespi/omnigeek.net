<?php

namespace Tests\Feature;

use App\Livewire\Feed;
use App\Livewire\TagFeed;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_posting_with_hashtags_creates_and_attaches_lowercase_tags(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Feed::class)
            ->set('body', 'Hello')
            ->set('tags', '#Rust #HomeLab homelab')
            ->call('save')
            ->assertHasNoErrors();

        $post = $user->posts()->first();
        $this->assertEqualsCanonicalizing(['rust', 'homelab'], $post->tags->pluck('name')->all());
        $this->assertDatabaseHas('tags', ['name' => 'rust', 'slug' => 'rust']);
    }

    public function test_differently_cased_tags_collapse_to_one_record(): void
    {
        Tag::fromText('Rust');
        Tag::fromText('rust');
        Tag::fromText('RUST');

        $this->assertSame(1, Tag::where('name', 'rust')->count());
    }

    public function test_tag_feed_shows_only_posts_with_that_tag(): void
    {
        $user = User::factory()->create();
        $tag = Tag::fromText('rust')->first();

        $tagged = $user->posts()->create(['body' => 'tagged post']);
        $tagged->tags()->attach($tag);
        $user->posts()->create(['body' => 'untagged post']);

        Livewire::test(TagFeed::class, ['tag' => $tag])
            ->assertSee('tagged post')
            ->assertDontSee('untagged post');
    }

    public function test_tag_is_resolved_by_slug(): void
    {
        $tag = Tag::fromText('#self-hosting')->first();

        $this->assertSame('self-hosting', $tag->name);
        $this->assertSame('self-hosting', $tag->slug);
        $this->get(route('tags.show', $tag))->assertOk();
    }

    public function test_a_post_with_no_hashtags_attaches_nothing(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Feed::class)
            ->set('body', 'no tags here')
            ->set('tags', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertCount(0, $user->posts()->first()->tags);
    }
}
