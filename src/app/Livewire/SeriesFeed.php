<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostModeration;
use App\Models\Series;
use Livewire\Component;
use Livewire\WithPagination;

class SeriesFeed extends Component
{
    use HandlesPostModeration;
    use WithPagination;

    public Series $series;

    public function mount(Series $series): void
    {
        $this->series = $series;
    }

    public function render()
    {
        $intro = $this->series->excerpt(160);

        $posts = $this->series->postsInOrder()
            ->withFeedRelations()
            ->published()
            ->paginate(15);

        return view('livewire.series-feed', [
            'posts' => $posts,
            // Where "start reading" goes: the first published part (page 1 already has it).
            'first' => $posts->currentPage() === 1
                ? $posts->first()
                : $this->series->postsInOrder()->published()->first(),
        ])->layout('layouts.app', [
            'ogTitle' => $this->series->name,
            'ogDescription' => $intro !== ''
                ? $intro
                : 'A series of posts: '.$this->series->name.' on '.config('app.name').'.',
        ]);
    }
}
