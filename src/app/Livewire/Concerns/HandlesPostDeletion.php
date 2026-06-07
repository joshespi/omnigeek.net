<?php

namespace App\Livewire\Concerns;

use App\Models\Post;

trait HandlesPostDeletion
{
    public function deletePost(Post $post): void
    {
        abort_unless($post->canDelete(auth()->user()), 403);

        $post->delete();

        $this->afterDelete();
    }

    protected function afterDelete(): void {}
}
