<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\TestCase;

class PostPreviewTest extends TestCase
{
    public function test_it_prefers_the_title(): void
    {
        $post = new Post(['title' => 'My title', 'body' => 'Some body text']);

        $this->assertSame('My title', $post->preview(80));
    }

    public function test_it_truncates_the_body_when_there_is_no_title(): void
    {
        $post = new Post(['title' => null, 'body' => str_repeat('a', 200)]);

        $this->assertSame(str_repeat('a', 80).'...', $post->preview(80));
    }

    public function test_it_returns_null_when_empty(): void
    {
        $post = new Post(['title' => null, 'body' => null]);

        $this->assertNull($post->preview(80));
    }
}
