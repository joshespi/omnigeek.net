<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostModeration;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class GeekProfile extends Component
{
    use HandlesPostModeration;
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function render()
    {
        $description = $this->user->bio
            ? str($this->user->bio)->limit(160)->toString()
            : $this->user->name.' on '.config('app.name').'.';

        return view('livewire.geek-profile', [
            'posts' => $this->user->posts()->withFeedRelations()->published()->latestForFeed()->paginate(15),
        ])->layout('layouts.app', [
            'ogTitle'       => $this->user->name,
            'ogDescription' => $description,
            'ogImage'       => $this->user->avatar_url,
            'ogUrl'         => route('geeks.show', $this->user),
        ]);
    }
}
