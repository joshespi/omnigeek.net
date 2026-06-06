<?php

namespace Tests\Unit;

use App\Models\Post;
use PHPUnit\Framework\TestCase;

class YoutubeParseTest extends TestCase
{
    public function test_it_extracts_ids_from_common_url_shapes(): void
    {
        $cases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
        ];

        foreach ($cases as $input => $expected) {
            $this->assertSame($expected, Post::parseYoutubeId($input), "Failed for: {$input}");
        }
    }

    public function test_it_returns_null_for_non_youtube_input(): void
    {
        $this->assertNull(Post::parseYoutubeId('https://example.com'));
        $this->assertNull(Post::parseYoutubeId('hello'));
        $this->assertNull(Post::parseYoutubeId(null));
    }
}
