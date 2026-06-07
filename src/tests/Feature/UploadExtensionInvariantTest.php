<?php

namespace Tests\Feature;

use App\Livewire\PostMediaEditor;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use App\Support\ImageProcessor;
use App\Support\PostMediaHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guards the upload-extension invariant.
 *
 * nginx forwards *.php to FPM and web-serves the upload volume. A file stored
 * with a .php extension would be executable. The only control preventing
 * upload-RCE is that the stored extension is derived from the server-detected
 * MIME type, never from the client-supplied filename.
 *
 * If these tests fail, an attacker can upload a PHP file and achieve RCE.
 */
class UploadExtensionInvariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_disguised_as_php_is_stored_with_safe_extension(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $file = UploadedFile::fake()->image('evil.php', 100, 100);

        $media = PostMediaHandler::attach($post, $file);

        $this->assertStringNotContainsString('.php', $media->path,
            'Stored path must not use the client-supplied .php extension');
        $this->assertMatchesRegularExpression('/\.(jpg|jpeg|png|webp)$/i', $media->path,
            'Stored image must land with a safe image extension');
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_image_with_double_extension_php_jpg_is_stored_safely(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $file = UploadedFile::fake()->image('shell.php.jpg', 100, 100);

        $media = PostMediaHandler::attach($post, $file);

        $this->assertStringNotContainsString('.php', $media->path,
            'Stored path must not contain .php anywhere in the filename');
    }

    public function test_post_media_editor_stores_uploaded_image_with_safe_extension(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(PostMediaEditor::class, ['post' => $post, 'editContext' => true])
            ->set('uploads', [UploadedFile::fake()->image('shell.png', 200, 200)])
            ->call('addMedia')
            ->assertHasNoErrors();

        $media = PostMedia::where('post_id', $post->id)->first();
        $this->assertNotNull($media);
        $this->assertStringNotContainsString('.php', $media->path,
            'media path stored in DB must not use the client-supplied .php extension');
        $this->assertMatchesRegularExpression('/\.(jpg|jpeg|png|webp)$/i', $media->path);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_stored_extension_is_derived_from_mime_not_filename(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        foreach (['backdoor.php', 'shell.phtml', 'cmd.php5'] as $filename) {
            $post = Post::factory()->for($user)->create();
            $file = UploadedFile::fake()->image($filename, 50, 50);
            $media = PostMediaHandler::attach($post, $file);

            $this->assertStringNotContainsString('.php', $media->path, "Failed for filename: $filename");
            $this->assertStringNotContainsString('.phtml', $media->path, "Failed for filename: $filename");
        }
    }
}
