<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostModeration;
use App\Models\Post;
use App\Models\Tag;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TagIndex extends Component
{
    use HandlesPostModeration;
    use WithPagination;

    #[Url(as: 'tags')]
    public array $selected = [];

    public function toggle(string $slug): void
    {
        $this->selected = in_array($slug, $this->selected, true)
            ? array_values(array_diff($this->selected, [$slug]))
            : [...$this->selected, $slug];

        $this->resetPage();
    }

    public function clear(): void
    {
        $this->selected = [];
        $this->resetPage();
    }

    public function render()
    {
        $posts = $this->selected === []
            ? null
            : Post::withFeedRelations()
                ->whereHas('tags', fn ($q) => $q->whereIn('slug', $this->selected))
                ->published()
                ->latestForFeed()
                ->paginate(15);

        return view('livewire.tag-index', [
            'tags' => Tag::has('posts')->withCount('posts')->orderBy('name')->get(),
            'posts' => $posts,
        ])->layout('layouts.app', [
            'ogTitle' => 'Browse by tag',
            'ogDescription' => 'Filter '.config('app.name').' posts by one or more tags.',
            'robots' => $this->selected === [] ? null : 'noindex,follow',
        ]);
    }
}
