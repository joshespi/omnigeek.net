<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ShowPost extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post->load('user', 'categories');
    }

    public function deletePost(Post $post): void
    {
        abort_unless($post->user_id === auth()->id(), 403);

        if ($post->media_path) {
            Storage::disk('public')->delete($post->media_path);
        }

        $post->delete();

        $this->redirect(route('home'), navigate: true);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.show-post');
    }
}
