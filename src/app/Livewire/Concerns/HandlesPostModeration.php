<?php

namespace App\Livewire\Concerns;

use App\Enums\Feed;
use App\Models\Post;

trait HandlesPostModeration
{
    public function deletePost(Post $post): void
    {
        abort_unless($post->canDelete(auth()->user()), 403);

        $post->delete();

        $this->afterDelete();
    }

    // Move a post between the main and memes feeds (same permission as delete — a
    // moderation action available to the author or an admin).
    public function moveToOtherFeed(Post $post): void
    {
        abort_unless($post->canDelete(auth()->user()), 403);

        $target = $post->oppositeFeed();
        $post->update(['feed' => $target]);

        $this->afterMove($post, $target);
    }

    protected function afterDelete(): void {}

    protected function afterMove(Post $post, Feed $target): void {}
}
