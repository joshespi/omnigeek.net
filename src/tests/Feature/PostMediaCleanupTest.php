<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use App\Support\PostMediaHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Stored media files must never orphan: every delete path that removes a
 * PostMedia row must also remove its file. Cleanup lives in model `deleting`
 * events (PostMedia/Post/User) rather than per-caller, so these guard all paths.
 */
class PostMediaCleanupTest extends TestCase
{
    use RefreshDatabase;

    private function attachImage(Post $post): PostMedia
    {
        return PostMediaHandler::attach($post, UploadedFile::fake()->image('g.jpg', 100, 100));
    }

    public function test_removing_one_item_deletes_its_file(): void
    {
        Storage::fake('public');
        $post = Post::factory()->create();
        $media = $this->attachImage($post);

        $media->delete();

        Storage::disk('public')->assertMissing($media->path);
        $this->assertModelMissing($media);
    }

    public function test_deleting_a_post_deletes_all_its_media_files(): void
    {
        Storage::fake('public');
        $post = Post::factory()->create();
        $a = $this->attachImage($post);
        $b = $this->attachImage($post);

        $post->delete();

        Storage::disk('public')->assertMissing($a->path);
        Storage::disk('public')->assertMissing($b->path);
        $this->assertSame(0, PostMedia::count());
    }

    public function test_deleting_a_user_cleans_up_their_posts_media_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $media = $this->attachImage($post);

        // The real leak: user delete DB-cascades posts -> media rows, but files
        // only vanish if the cascade routes through the model deleting events.
        $user->delete();

        Storage::disk('public')->assertMissing($media->path);
        $this->assertSame(0, PostMedia::count());
        $this->assertSame(0, Post::count());
    }
}
