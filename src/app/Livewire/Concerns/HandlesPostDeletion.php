<?php

namespace App\Livewire\Concerns;

use App\Models\Post;
use App\Support\PostMediaHandler;

trait HandlesPostDeletion
{
    public function deletePost(Post $post): void
    {
        abort_unless($post->canDelete(auth()->user()), 403);

        PostMediaHandler::delete($post->media_path);

        $post->delete();

        $this->afterDelete();
    }

    protected function afterDelete(): void {}
}
