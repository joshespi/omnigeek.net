<?php

namespace Tests\Feature;

use App\Enums\Feed;
use App\Livewire\ComposePost;
use App\Livewire\Feed as FeedComponent;
use App\Livewire\ShowPost;
use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Post create / delete / move are logged from the shared paths (not just the
 * admin screen), so the activity log reflects normal-UI moderation too.
 */
class PostActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_post_is_logged(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.body', 'a fresh post')
            ->call('save');

        $entry = ActivityLog::where('action', 'post.create')->first();
        $this->assertNotNull($entry);
        $this->assertSame($user->id, $entry->user_id);
        $this->assertSame(Feed::Main->value, $entry->meta['feed']);
    }

    public function test_deleting_a_post_from_a_feed_is_logged(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['title' => 'doomed']);

        Livewire::actingAs($user)
            ->test(FeedComponent::class)
            ->call('deletePost', $post);

        $entry = ActivityLog::where('action', 'post.delete')->first();
        $this->assertNotNull($entry);
        $this->assertSame('doomed', $entry->subject_label);
    }

    public function test_moving_a_post_to_memes_is_logged_with_destination(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(ShowPost::class, ['post' => $post])
            ->call('moveToOtherFeed', $post);

        $entry = ActivityLog::where('action', 'post.move')->first();
        $this->assertNotNull($entry);
        $this->assertSame(Feed::Memes->value, $entry->meta['to']);
    }

    public function test_editing_a_post_does_not_log_a_create(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\PostEditor::class, ['post' => $post])
            ->call('startEditing')
            ->set('form.body', 'edited body')
            ->call('save');

        $this->assertSame(0, ActivityLog::where('action', 'post.create')->count());
    }
}
