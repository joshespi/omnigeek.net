<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostDeletion;
use App\Models\User;
use Livewire\Attributes\Layout;
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

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.geek-profile', [
            'posts' => $this->user->posts()->with('user', 'categories')->latest()->paginate(15),
        ]);
    }
}
