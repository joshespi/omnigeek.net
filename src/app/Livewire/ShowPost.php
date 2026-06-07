<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostDeletion;
use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    use HandlesPostDeletion;

    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post->load('user', 'categories', 'tags', 'media');
        $post->increment('view_count');
    }

    protected function afterDelete(): void
    {
        $this->redirect(route('home'), navigate: true);
    }

    public function render()
    {
        $description = $this->post->body
            ? str($this->post->body)->limit(160)->toString()
            : 'A post by '.$this->post->user->name.' on '.config('app.name').'.';

        $firstImage = $this->post->media->firstWhere('type', 'image');
        $image = $firstImage?->url();

        return view('livewire.show-post')->layout('layouts.app', [
            'ogTitle'       => $this->post->user->name.' on '.config('app.name'),
            'ogDescription' => $description,
            'ogImage'       => $image,
            'ogUrl'         => route('posts.show', $this->post),
        ]);
    }
}
