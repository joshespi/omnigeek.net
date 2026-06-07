<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostDeletion;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class GeekProfile extends Component
{
    use HandlesPostDeletion;
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
            'posts' => $this->user->posts()->withFeedRelations()->published()->orderByRaw('COALESCE(published_at, created_at) DESC')->paginate(15),
        ])->layout('layouts.app', [
            'ogTitle'       => $this->user->name,
            'ogDescription' => $description,
            'ogImage'       => $this->user->avatar_url,
            'ogUrl'         => route('geeks.show', $this->user),
        ]);
    }
}
