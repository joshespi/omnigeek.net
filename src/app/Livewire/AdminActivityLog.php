<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminActivityLog extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    public function render()
    {
        $this->authorize('admin');

        return view('livewire.admin-activity-log', [
            'entries' => ActivityLog::with('user')->latest('created_at')->paginate(50),
        ]);
    }
}
