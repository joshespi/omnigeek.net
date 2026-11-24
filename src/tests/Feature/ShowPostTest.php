<?php

namespace Tests\Feature;

use App\Enums\Feed;
use App\Livewire\ShowPost;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
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
            ->assertSee('Copy link to share elsewhere')
            ->assertSee('Share on Bluesky');
    }

    public function test_an_edited_post_shows_the_badge_and_edit_list(): void
    {
        $post = Post::factory()->create(['body' => 'Body text']);
        $post->edits()->create(['note' => 'Corrected the venue', 'edited_at' => now()]);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('edited-badge', false)
            ->assertSee('Corrected the venue');
    }

    public function test_an_unedited_post_has_no_edit_badge_or_list(): void
    {
        $post = Post::factory()->create(['body' => 'Body text']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertDontSee('edited-badge', false)
            ->assertDontSee('id="edits"', false);
    }

    public function test_the_home_feed_tags_edited_posts(): void
    {
        $post = Post::factory()->create(['title' => 'Edited story', 'body' => 'Body text']);
        $post->edits()->create(['note' => 'Tweaked', 'edited_at' => now()]);

        $this->get(route('home'))->assertOk()->assertSee('edited-badge', false);
    }

    public function test_related_posts_match_categories_or_tags_and_exclude_unpublished_posts(): void
    {
        $category = Category::create(['name' => 'Games']);
        $tag = Tag::create(['name' => 'Co-op']);
        $post = Post::factory()->create(['title' => 'The current story']);
        $post->categories()->attach($category);
        $post->tags()->attach($tag);

        Post::factory()->create(['title' => 'Category match'])->categories()->attach($category);
        Post::factory()->create(['title' => 'Tag match'])->tags()->attach($tag);
        Post::factory()->create(['title' => 'Upcoming story', 'published_at' => now()->addDay()])
            ->categories()->attach($category);
        Post::factory()->create(['title' => 'Unrelated story']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('Related posts')
            ->assertSee('Category match')
            ->assertSee('Tag match')
            ->assertDontSee('Upcoming story')
            ->assertDontSee('Unrelated story');
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
