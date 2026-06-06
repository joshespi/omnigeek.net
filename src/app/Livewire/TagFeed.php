<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostDeletion;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TagFeed extends Component
{
    use HandlesPostDeletion;
    use WithPagination;

    public Tag $tag;

    public function mount(Tag $tag): void
    {
        $this->tag = $tag;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.tag-feed', [
            'posts' => $this->tag->posts()->withFeedRelations()->latest()->paginate(15),
        ]);
    }
}
