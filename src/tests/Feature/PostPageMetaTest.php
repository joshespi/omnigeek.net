<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\PostMediaHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostPageMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_scheduled_post_is_a_404_for_guests_and_strangers(): void
    {
        $post = Post::factory()->create(['published_at' => now()->addDay()]);

        $this->get(route('posts.show', $post))->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('posts.show', $post))->assertNotFound();
        $this->assertSame(0, $post->fresh()->view_count);
    }

    public function test_a_scheduled_post_is_visible_to_its_author_and_admins_but_noindexed(): void
    {
        $post = Post::factory()->create(['published_at' => now()->addDay()]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($post->user)->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('noindex,nofollow', false);
        $this->actingAs($admin)->get(route('posts.show', $post))->assertOk();

        // Previewing a not-yet-live post doesn't count as a view.
        $this->assertSame(0, $post->fresh()->view_count);
    }

    public function test_a_view_counts_without_touching_updated_at(): void
    {
        $post = Post::factory()->create();
        $post->forceFill(['updated_at' => now()->subYear()])->saveQuietly();
        $before = $post->fresh()->updated_at;

        $this->get(route('posts.show', $post))->assertOk();

        $fresh = $post->fresh();
        $this->assertSame(1, $fresh->view_count);
        $this->assertTrue($fresh->updated_at->equalTo($before));
    }

    public function test_crawlers_do_not_inflate_the_view_count(): void
    {
        $post = Post::factory()->create();

        $this->get(route('posts.show', $post), ['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)'])->assertOk();

        $this->assertSame(0, $post->fresh()->view_count);
    }

    public function test_a_phone_whose_model_name_contains_bot_still_counts(): void
    {
        $post = Post::factory()->create();

        $this->get(route('posts.show', $post), ['User-Agent' => 'Mozilla/5.0 (Linux; Android 12; CUBOT X30 Build/SP1A) Mobile Safari/537.36'])->assertOk();

        $this->assertSame(1, $post->fresh()->view_count);
    }

    public function test_an_image_only_body_previews_as_its_alt_text(): void
    {
        $post = new Post(['body' => '![A very good cat](https://example.com/cat.jpg)']);

        $this->assertSame('A very good cat', $post->preview(80));
    }

    public function test_plain_body_tracks_edits_to_the_body(): void
    {
        $post = new Post(['body' => '**old**']);
        $this->assertSame('old', $post->plainBody());

        $post->body = '_new_';
        $this->assertSame('new', $post->plainBody());
    }

    public function test_nsfw_memes_do_not_leak_their_image_or_get_indexed(): void
    {
        Storage::fake('public');
        $post = Post::factory()->nsfw()->create();
        $media = PostMediaHandler::attach($post, UploadedFile::fake()->image('spicy.jpg', 100, 100));

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('noindex,nofollow', false)
            ->assertDontSee('<meta property="og:image" content="'.$media->url().'"', false)
            ->assertDontSee('<meta name="twitter:image" content="'.$media->url().'"', false);
    }

    public function test_a_normal_post_uses_its_image_and_is_an_article(): void
    {
        Storage::fake('public');
        $post = Post::factory()->create();
        $media = PostMediaHandler::attach($post, UploadedFile::fake()->image('ok.jpg', 100, 100));

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.$media->url().'"', false)
            ->assertSee('property="og:type"', false)
            ->assertSee('content="article"', false)
            ->assertSee('article:published_time', false)
            ->assertSee('index,follow', false);
    }

    public function test_title_and_markdown_free_description_are_used_for_meta(): void
    {
        $post = Post::factory()->create([
            'title' => 'A Fine Title',
            'body' => "**Bold** claim with a [link](https://example.com) and `code`",
        ]);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('<title>A Fine Title — '.config('app.name').'</title>', false)
            ->assertSee('<meta name="description" content="Bold claim with a link and code">', false)
            ->assertSee('"articleBody":"Bold claim with a link and code"', false);
    }

    public function test_listing_pages_get_their_own_titles(): void
    {
        $category = Category::create(['name' => 'Retro Gaming', 'slug' => 'retro-gaming']);
        $tag = Tag::create(['name' => 'zelda', 'slug' => 'zelda']);

        $this->get(route('categories.show', $category))->assertSee('<title>Retro Gaming — ', false);
        $this->get(route('tags.show', $tag))->assertSee('<title>#zelda — ', false);
        $this->get(route('memes'))->assertSee('<title>Memes — ', false);
        $this->get(route('subscribe'))->assertSee('<title>Subscribe — ', false);
        $this->get(route('categories.index'))->assertSee('<title>Categories — ', false);
    }

    public function test_unknown_pages_render_the_branded_404(): void
    {
        $this->get('/definitely-not-a-page')
            ->assertNotFound()
            ->assertSee('Page not found');
    }

    public function test_the_home_feed_has_an_h1(): void
    {
        $this->get(route('home'))->assertSee('<h1', false);
    }
}
