<?php

namespace App\Livewire\Concerns;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;

trait HandlesPostDeletion
{
    public function deletePost(Post $post): void
    {
        abort_unless($post->user_id === auth()->id(), 403);

        if ($post->media_path) {
            Storage::disk('public')->delete($post->media_path);
        }

        $post->delete();
    }
}
