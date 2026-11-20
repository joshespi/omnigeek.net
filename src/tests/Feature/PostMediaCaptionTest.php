<?php

namespace Tests\Feature;

use App\Livewire\PostMediaEditor;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PostMediaCaptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function image(Post $post, ?string $caption = null, int $order = 1): PostMedia
    {
        return $post->media()->create([
            'path' => "posts/shot-{$order}.jpg",
            'type' => 'image',
            'caption' => $caption,
            'sort_order' => $order,
        ]);
    }

    public function test_the_owner_can_caption_an_image(): void
    {
        $post = Post::factory()->create();
        $image = $this->image($post);

        Livewire::actingAs($post->user)
            ->test(PostMediaEditor::class, ['post' => $post])
            ->assertSet("captions.{$image->id}", '')
            ->set("captions.{$image->id}", '  The tavern at Brill, the first night.  ')
            ->call('saveCaption', $image->id)
            ->assertHasNoErrors();

        $this->assertSame('The tavern at Brill, the first night.', $image->refresh()->caption);
    }

    public function test_clearing_a_caption_removes_it(): void
    {
        $post = Post::factory()->create();
        $image = $this->image($post, 'Old caption');

        Livewire::actingAs($post->user)
            ->test(PostMediaEditor::class, ['post' => $post])
            ->assertSet("captions.{$image->id}", 'Old caption')
            ->set("captions.{$image->id}", '   ')
            ->call('saveCaption', $image->id);

        $this->assertNull($image->refresh()->caption);
    }

    public function test_a_caption_over_the_limit_is_rejected(): void
    {
        $post = Post::factory()->create();
        $image = $this->image($post, 'Kept');

        Livewire::actingAs($post->user)
            ->test(PostMediaEditor::class, ['post' => $post])
            ->set("captions.{$image->id}", str_repeat('a', 501))
            ->call('saveCaption', $image->id)
            ->assertHasErrors("captions.{$image->id}");

        $this->assertSame('Kept', $image->refresh()->caption);
    }

    public function test_someone_else_cannot_caption_the_image(): void
    {
        $post = Post::factory()->create();
        $image = $this->image($post);

        Livewire::actingAs(User::factory()->create())
            ->test(PostMediaEditor::class, ['post' => $post])
            ->set("captions.{$image->id}", 'Not mine to write')
            ->call('saveCaption', $image->id)
            ->assertForbidden();

        $this->assertNull($image->refresh()->caption);
    }

    public function test_an_image_from_another_post_cannot_be_captioned_through_this_one(): void
    {
        $post = Post::factory()->create();
        $other = $this->image(Post::factory()->create());

        Livewire::actingAs($post->user)
            ->test(PostMediaEditor::class, ['post' => $post])
            ->set("captions.{$other->id}", 'Wrong post')
            ->call('saveCaption', $other->id)
            ->assertForbidden();

        $this->assertNull($other->refresh()->caption);
    }

    public function test_a_caption_shows_under_the_image_and_as_its_alt_text(): void
    {
        $post = Post::factory()->create(['title' => 'Before the Record']);
        $this->image($post, 'The fire did nothing for me. I stayed by it anyway.');

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('<p>The fire did nothing for me. I stayed by it anyway.</p>', false)
            ->assertSee('alt="The fire did nothing for me. I stayed by it anyway."', false);
    }

    public function test_several_images_list_only_the_captions_that_exist(): void
    {
        $post = Post::factory()->create(['title' => 'Ragefire']);
        $this->image($post, 'The chief of the troggs.', 1);
        $this->image($post, null, 2);
        $this->image($post, 'The demon in the fire.', 3);

        $html = $this->get(route('posts.show', $post))->assertOk()->getContent();

        $this->assertStringContainsString('1.</span> The chief of the troggs.', $html);
        $this->assertStringContainsString('3.</span> The demon in the fire.', $html);
        $this->assertStringNotContainsString('2.</span>', $html);
        // The uncaptioned one still gets descriptive alt text from its post.
        $this->assertStringContainsString('alt="Ragefire (2 of 3)"', $html);
    }

    public function test_an_image_without_a_caption_renders_no_caption_block(): void
    {
        $post = Post::factory()->create(['title' => 'Plain']);
        $this->image($post);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertDontSee('italic text-gray-600', false);
    }
}
