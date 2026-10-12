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
        return view('livewire.series-feed', [
            'posts' => $this->series->postsInOrder()
                ->withFeedRelations()
                ->published()
                ->paginate(15),
        ])->layout('layouts.app', [
            'ogTitle' => $this->series->name,
            'ogDescription' => 'A series of posts: '.$this->series->name.' on '.config('app.name').'.',
        ]);
    }
}
