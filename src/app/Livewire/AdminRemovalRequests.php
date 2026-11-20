<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\RemovalRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminRemovalRequests extends Component
{
    use WithPagination;

    public function resolve(RemovalRequest $request): void
    {
        $this->authorize('admin');

        $request->update(['resolved_at' => now()]);

        ActivityLog::record('removal_request.resolve', 'removal_request', $request->id, $request->kindLabel());
    }

    public function reopen(RemovalRequest $request): void
    {
        $this->authorize('admin');

        $request->update(['resolved_at' => null]);

        ActivityLog::record('removal_request.reopen', 'removal_request', $request->id, $request->kindLabel());
    }

    public function delete(RemovalRequest $request): void
    {
        $this->authorize('admin');

        ActivityLog::record('removal_request.delete', 'removal_request', $request->id, $request->kindLabel());

        $request->delete();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $this->authorize('admin');

        return view('livewire.admin-removal-requests', [
            // Open requests first, oldest first, so nothing waits behind newer ones.
            'requests' => RemovalRequest::with('post')
                ->orderByRaw('resolved_at IS NOT NULL')
                ->orderBy('created_at')
                ->paginate(20),
        ]);
    }
}
