<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostModeration;
use App\Models\Post;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Search extends Component
{
    use HandlesPostModeration;
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

    public function render()
    {
        // 3, not 2: MySQL/MariaDB's default innodb_ft_min_token_size means a 2-char
        // query can never match anything in the FULLTEXT index in production — no
        // point running a query (or showing "no results") for a length that's
        // structurally guaranteed to be empty there.
        $posts = strlen(trim($this->query)) >= 3
            ? Post::withFeedRelations()->search($this->query)->published()->latestForFeed()->paginate(15)
            : null;

        // Search results are per-query duplicate/thin content — keep them out of the index.
        return view('livewire.search', ['posts' => $posts])
            ->layout('layouts.app', ['ogTitle' => 'Search', 'robots' => 'noindex,follow']);
    }
}
