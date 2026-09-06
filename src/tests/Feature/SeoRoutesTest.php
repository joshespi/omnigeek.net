<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_points_at_the_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee(route('sitemap'));
    }

    public function test_sitemap_lists_published_posts_categories_and_tags(): void
    {
        $post = Post::factory()->create(['title' => 'Sitemap post']);
        $category = Category::create(['name' => 'Sitemap category']);
        $post->categories()->attach($category);
        $tag = Tag::fromText('sitemaptag')->first();
        $post->tags()->attach($tag);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $response->assertSee(route('posts.show', $post), false);
        $response->assertSee(route('categories.show', $category), false);
        $response->assertSee(route('tags.show', $tag), false);
    }

    public function test_sitemap_excludes_nsfw_memes(): void
    {
        $meme = Post::factory()->create(['feed' => \App\Enums\Feed::Memes, 'nsfw' => true]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertDontSee(route('posts.show', $meme), false);
    }

    public function test_rss_feed_lists_published_posts(): void
    {
        $post = Post::factory()->create(['title' => 'Feed post', 'body' => 'Some **markdown** body.']);

        $response = $this->get(route('feed'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $response->assertSee('Feed post');
        $response->assertSee(route('posts.show', $post), false);
    }

    public function test_rss_feed_excludes_memes(): void
    {
        $meme = Post::factory()->create(['feed' => \App\Enums\Feed::Memes, 'title' => 'A meme post']);

        $response = $this->get(route('feed'));

        $response->assertOk();
        $response->assertDontSee(route('posts.show', $meme), false);
    }
}
