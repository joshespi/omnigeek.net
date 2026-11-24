<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostModeration;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Feed extends Component
{
    use HandlesPostModeration;
    use WithPagination;

    #[On('post-created')]
    public function onPostCreated(): void
    {
        $this->resetPage();
    }

    protected function afterDelete(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $posts = Post::with('user', 'categories', 'media')->published()->latestForFeed()->latest('id')->paginate(15);

        return view('livewire.feed', [
            'posts' => $posts,
            'featured' => $posts->onFirstPage() ? $posts->first() : null,
        ]);
    }
}
