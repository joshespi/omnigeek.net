<?php

namespace Tests\Feature;

use App\Livewire\ComposePost;
use App\Models\User;
use App\Support\Markdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InlineMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_markdown_renders_an_inline_image(): void
    {
        $html = Markdown::render('![a cat](/storage/uploads/cat.jpg)');

        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('src="/storage/uploads/cat.jpg"', $html);
        $this->assertStringContainsString('alt="a cat"', $html);
    }

    public function test_native_emoji_survive_rendering(): void
    {
        $html = Markdown::render('shipped it 🚀🎉');

        $this->assertStringContainsString('🚀', $html);
        $this->assertStringContainsString('🎉', $html);
    }

    public function test_an_unsafe_protocol_image_src_is_neutralised(): void
    {
        // allow_unsafe_links => false: a javascript: image must not survive as a live src.
        $html = Markdown::render('![x](javascript:alert(1))');

        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_uploading_an_inline_image_stores_it_and_returns_a_url(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(ComposePost::class)
            ->set('inlineImage', UploadedFile::fake()->image('shot.png'))
            ->assertDispatched('inline-image-uploaded', function ($event, $params) {
                return str_contains($params['url'], '/storage/uploads/');
            })
            ->assertHasNoErrors('inlineImage');

        // Exactly one file landed in the uploads dir, re-encoded by ImageProcessor.
        $this->assertCount(1, Storage::disk('public')->files('uploads'));
    }

    public function test_a_non_image_inline_upload_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(ComposePost::class)
            ->set('inlineImage', UploadedFile::fake()->create('virus.pdf', 100))
            ->assertHasErrors('inlineImage')
            ->assertNotDispatched('inline-image-uploaded');

        $this->assertEmpty(Storage::disk('public')->files('uploads'));
    }
}
