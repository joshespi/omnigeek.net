<?php

namespace Tests\Feature;

use App\Livewire\GeekProfile;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GeekProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_the_users_bio_and_only_their_posts(): void
    {
        $geek = User::factory()->create(['name' => 'Ada Pixel', 'bio' => 'Retro hardware tinkerer.']);
        $other = User::factory()->create();

        $geek->posts()->create(['body' => 'My own post']);
        $other->posts()->create(['body' => 'Someone elses post']);

        Livewire::test(GeekProfile::class, ['user' => $geek])
            ->assertSee('Ada Pixel')
            ->assertSee('Retro hardware tinkerer.')
            ->assertSee('My own post')
            ->assertDontSee('Someone elses post');
    }

    public function test_profile_page_is_publicly_reachable(): void
    {
        $geek = User::factory()->create();

        $this->get(route('geeks.show', $geek))->assertOk();
    }

    public function test_only_the_owner_can_delete_their_post_from_the_profile(): void
    {
        $geek = User::factory()->create();
        $post = $geek->posts()->create(['body' => 'mine']);

        Livewire::actingAs($geek)
            ->test(GeekProfile::class, ['user' => $geek])
            ->call('deletePost', $post)
            ->assertHasNoErrors();

        $this->assertModelMissing($post);
    }

    public function test_a_stranger_cannot_delete_a_post(): void
    {
        $geek = User::factory()->create();
        $stranger = User::factory()->create();
        $post = $geek->posts()->create(['body' => 'mine']);

        Livewire::actingAs($stranger)
            ->test(GeekProfile::class, ['user' => $geek])
            ->call('deletePost', $post)
            ->assertForbidden();

        $this->assertModelExists($post);
    }
}
