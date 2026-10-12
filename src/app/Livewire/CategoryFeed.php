<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostModeration;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryFeed extends Component
{
    use HandlesPostModeration;
    use WithPagination;

    public Category $category;

    public function mount(Category $category): void
    {
        $this->category = $category;
    }

    public function render()
    {
        return view('livewire.category-feed', [
            'posts' => $this->category->posts()->withFeedRelations()->published()->latestForFeed()->paginate(15),
        ])->layout('layouts.app', [
            'ogTitle' => $this->category->name,
            'ogDescription' => 'Posts filed under '.$this->category->name.' on '.config('app.name').'.',
        ]);
    }
}
