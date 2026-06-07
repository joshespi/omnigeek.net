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
        return view('livewire.feed', [
            'posts' => Post::withFeedRelations()->published()->latestForFeed()->paginate(15),
        ]);
    }
}
