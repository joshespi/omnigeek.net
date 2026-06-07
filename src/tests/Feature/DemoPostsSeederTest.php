<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use Database\Seeders\DemoPostsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoPostsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_one_post_of_every_type(): void
    {
        Storage::fake('public');

        $this->seed(DemoPostsSeeder::class);

        $this->assertGreaterThanOrEqual(1, Post::whereNotNull('body')->doesntHave('media')->whereNull('youtube_id')->count());
        $this->assertGreaterThanOrEqual(1, PostMedia::where('type', 'image')->count());
        $this->assertGreaterThanOrEqual(1, PostMedia::where('type', 'video')->count());
        $this->assertGreaterThanOrEqual(1, Post::whereNotNull('youtube_id')->count());

        $image = PostMedia::where('type', 'image')->first();
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_it_seeds_multiple_geeks_with_bios(): void
    {
        Storage::fake('public');

        $this->seed(DemoPostsSeeder::class);

        $this->assertGreaterThanOrEqual(5, User::has('posts')->count());
        $this->assertGreaterThanOrEqual(4, User::whereNotNull('bio')->count());
    }

    public function test_it_is_idempotent(): void
    {
        Storage::fake('public');

        $this->seed(DemoPostsSeeder::class);
        $users = User::count();
        $posts = Post::count();

        $this->seed(DemoPostsSeeder::class);

        $this->assertSame($users, User::count());
        $this->assertSame($posts, Post::count());
    }
}
