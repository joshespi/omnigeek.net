<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostDeletion;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryFeed extends Component
{
    use HandlesPostDeletion;
    use WithPagination;

    public Category $category;

    public function mount(Category $category): void
    {
        $this->category = $category;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.category-feed', [
            'posts' => $this->category->posts()->withFeedRelations()->latest()->paginate(15),
        ]);
    }
}
