<?php

namespace Tests\Feature;

use App\Livewire\ShowPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_view_a_single_post(): void
    {
        $post = Post::factory()->create(['body' => 'A shareable post']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('A shareable post')
            ->assertSee('Copy link to this post');
    }

    public function test_an_author_can_delete_from_the_post_page(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(ShowPost::class, ['post' => $post])
            ->call('deletePost', $post)
            ->assertRedirect(route('home'));

        $this->assertModelMissing($post);
    }

    public function test_a_non_author_cannot_delete_from_the_post_page(): void
    {
        $post = Post::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ShowPost::class, ['post' => $post])
            ->call('deletePost', $post)
            ->assertForbidden();

        $this->assertModelExists($post);
    }
}
