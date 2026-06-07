<?php

namespace Tests\Feature;

use App\Livewire\Search;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_short_queries_return_no_results(): void
    {
        Post::factory()->create(['body' => 'findable content']);

        // < 2 chars: render returns null posts, nothing matched.
        Livewire::test(Search::class)
            ->set('query', 'f')
            ->assertViewHas('posts', null);
    }

    public function test_a_query_matches_post_body(): void
    {
        $match = Post::factory()->create(['body' => 'unique homelab rack']);
        $other = Post::factory()->create(['body' => 'totally different']);

        Livewire::test(Search::class)
            ->set('query', 'homelab')
            ->assertSee('unique homelab rack')
            ->assertDontSee('totally different');
    }

    public function test_a_query_matches_author_name(): void
    {
        $ada = User::factory()->create(['name' => 'Ada Pixel']);
        Post::factory()->for($ada)->create(['body' => 'no keyword here']);

        Livewire::test(Search::class)
            ->set('query', 'Ada')
            ->assertSee('no keyword here');
    }
}
