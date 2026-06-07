<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostDeletion;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Search extends Component
{
    use HandlesPostDeletion;
    use WithPagination;

    #[Url(as: 'q')]
    public string $query = '';

    public function updatingQuery(): void
    {
        $this->resetPage();
    }

    protected function afterDelete(): void
    {
        // stay on results page
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $posts = strlen(trim($this->query)) >= 2
            ? Post::withFeedRelations()->search($this->query)->published()->latestForFeed()->paginate(15)
            : null;

        return view('livewire.search', ['posts' => $posts]);
    }
}
