<?php

namespace Tests\Feature;

use App\Livewire\ComposePost;
use App\Livewire\Feed;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_post_text(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.body', 'Hello world')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'body' => 'Hello world',
        ]);
    }

    public function test_an_empty_post_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.body', '   ')
            ->call('save')
            ->assertHasErrors('form.body');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_a_youtube_url_is_parsed_into_an_id(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.youtube', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', ['youtube_id' => 'dQw4w9WgXcQ']);
    }

    public function test_an_invalid_youtube_url_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.youtube', 'not a youtube link')
            ->call('save')
            ->assertHasErrors('form.youtube');
    }

    public function test_an_image_upload_is_stored(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('media', [UploadedFile::fake()->image('photo.jpg')])
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::first();
        $media = $post->media()->first();
        $this->assertNotNull($media);
        $this->assertSame('image', $media->type);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_a_video_upload_is_stored_untouched(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('media', [UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4')])
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::first();
        $media = $post->media()->first();
        $this->assertNotNull($media);
        $this->assertSame('video', $media->type);
        $this->assertStringEndsWith('.mp4', $media->path);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_a_user_can_delete_their_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(Feed::class)
            ->call('deletePost', $post);

        $this->assertModelMissing($post);
    }

    public function test_a_user_cannot_delete_someone_elses_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        Livewire::actingAs($other)
            ->test(Feed::class)
            ->call('deletePost', $post)
            ->assertForbidden();

        $this->assertModelExists($post);
    }

    public function test_guests_can_view_the_feed(): void
    {
        $post = Post::factory()->create(['body' => 'Public hello']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Public hello')
            ->assertSee('Log in');
    }

    public function test_guests_cannot_post(): void
    {
        Livewire::test(ComposePost::class)
            ->set('form.body', 'sneaky guest post')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_a_post_can_have_an_optional_title(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.title', 'My Review')
            ->set('form.body', 'Great book.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', ['title' => 'My Review', 'body' => 'Great book.']);
    }

    public function test_body_accepts_up_to_10000_characters(): void
    {
        $user = User::factory()->create();
        $longBody = str_repeat('a', 10000);

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.body', $longBody)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', ['body' => $longBody]);
    }

    public function test_body_over_10000_characters_is_rejected(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ComposePost::class)
            ->set('form.body', str_repeat('a', 10001))
            ->call('save')
            ->assertHasErrors('form.body');
    }
}
