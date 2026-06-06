<?php

namespace App\Livewire;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class AdminUsers extends Component
{
    use WithPagination;

    #[Validate('nullable|email|max:255')]
    public string $inviteEmail = '';

    #[Validate('nullable|integer|min:0|max:365')]
    public ?int $inviteDays = 14;

    public ?string $inviteLink = null;

    public ?int $editingId = null;
    public string $editingName = '';
    public string $editingEmail = '';
    public string $editingBio = '';

    public function sendInvite(): void
    {
        $this->authorize('admin');
        $this->validate();

        $invite = Invite::mint(
            email: $this->inviteEmail ?: null,
            createdBy: auth()->id(),
            expiresInDays: $this->inviteDays > 0 ? $this->inviteDays : null,
        );

        $this->inviteLink = url('/register?invite='.$invite->token);
        $this->reset('inviteEmail', 'inviteDays');
    }

    public function edit(User $user): void
    {
        $this->authorize('admin');

        $this->editingId = $user->id;
        $this->editingName = $user->name;
        $this->editingEmail = $user->email;
        $this->editingBio = $user->bio ?? '';
    }

    public function update(): void
    {
        $this->authorize('admin');

        $user = User::findOrFail($this->editingId);

        $this->validate([
            'editingName' => 'required|string|max:255',
            'editingEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'editingBio' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $this->editingName,
            'email' => $this->editingEmail,
            'bio' => $this->editingBio ?: null,
        ]);

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editingName', 'editingEmail', 'editingBio');
    }

    public function toggleAdmin(User $user): void
    {
        $this->authorize('admin');

        abort_if($user->id === auth()->id(), 403, 'Cannot change your own admin status.');

        $user->update(['is_admin' => ! $user->is_admin]);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $this->authorize('admin');

        return view('livewire.admin-users', [
            'users' => User::withCount('posts')->orderBy('name')->paginate(25),
        ]);
    }
}
