<?php

namespace Tests\Feature;

use App\Livewire\PostEditor;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_edit_their_post_body(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['body' => 'original']);

        Livewire::actingAs($user)
            ->test(PostEditor::class, ['post' => $post])
            ->call('startEditing')
            ->assertSet('form.body', 'original')
            ->set('form.body', 'edited')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('editing', false);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'body' => 'edited']);
    }

    public function test_editing_syncs_categories_and_tags(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['body' => 'original']);
        $games = Category::create(['name' => 'Video Games']);

        Livewire::actingAs($user)
            ->test(PostEditor::class, ['post' => $post])
            ->call('startEditing')
            ->set('form.selectedCategories', [$games->id])
            ->set('form.tags', '#rust homelab')
            ->call('save')
            ->assertHasNoErrors();

        $post->refresh();
        $this->assertEqualsCanonicalizing([$games->id], $post->categories->pluck('id')->all());
        $this->assertEqualsCanonicalizing(['rust', 'homelab'], $post->tags->pluck('name')->all());
    }

    public function test_canceling_editing_resets_the_form_without_error(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['body' => 'original']);

        Livewire::actingAs($user)
            ->test(PostEditor::class, ['post' => $post])
            ->call('startEditing')
            ->set('form.body', 'unsaved edit')
            ->call('cancelEditing')
            ->assertHasNoErrors()
            ->assertSet('editing', false)
            ->assertSet('showPreview', false);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'body' => 'original']);
    }

    public function test_a_non_owner_cannot_start_editing(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        Livewire::actingAs($other)
            ->test(PostEditor::class, ['post' => $post])
            ->call('startEditing')
            ->assertForbidden();
    }

    public function test_an_admin_cannot_edit_someone_elses_post_inline(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->for($owner)->create();

        Livewire::actingAs($admin)
            ->test(PostEditor::class, ['post' => $post])
            ->call('startEditing')
            ->assertForbidden();
    }

    public function test_editing_an_existing_youtube_post_keeps_the_id(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create([
            'body' => null,
            'youtube_id' => 'dQw4w9WgXcQ',
        ]);

        Livewire::actingAs($user)
            ->test(PostEditor::class, ['post' => $post])
            ->call('startEditing')
            ->assertSet('form.youtube', 'dQw4w9WgXcQ')
            ->set('form.title', 'Classic')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'youtube_id' => 'dQw4w9WgXcQ', 'title' => 'Classic']);
    }
}
