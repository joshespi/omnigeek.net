<?php

namespace Tests\Feature;

use App\Livewire\AdminPosts;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_non_admins_cannot_reach_admin_posts(): void
    {
        $this->get(route('admin.posts'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.posts'))
            ->assertForbidden();
    }

    public function test_admin_can_reach_admin_posts(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.posts'))
            ->assertOk();
    }

    public function test_admin_can_delete_another_users_post(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create(['body' => 'hello']);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(AdminPosts::class)
            ->call('deletePost', $post);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_admin_can_edit_post_body_and_categories(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create(['body' => 'original']);
        $category = Category::create(['name' => 'Gaming']);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(AdminPosts::class)
            ->call('edit', $post)
            ->set('form.body', 'updated body')
            ->set('form.selectedCategories', [$category->id])
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'body' => 'updated body']);
        $this->assertDatabaseHas('category_post', ['post_id' => $post->id, 'category_id' => $category->id]);
    }

    public function test_non_admin_cannot_use_admin_posts_component(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(AdminPosts::class)
            ->assertForbidden();
    }

    public function test_admin_delete_button_hidden_on_feed(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();
        Post::factory()->for($other)->create(['body' => 'someone elses post']);

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertDontSee('Delete');
    }

    public function test_admin_delete_button_visible_on_post_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();
        $post = Post::factory()->for($other)->create(['body' => 'someone elses post']);

        $this->actingAs($admin)
            ->get(route('posts.show', $post))
            ->assertSee('Delete');
    }
}
