<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $avatar = null;

    public function updateAvatar(): void
    {
        $this->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        $user = Auth::user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->forceFill([
            'avatar_path' => $this->avatar->store('avatars', 'public'),
        ])->save();

        $this->reset('avatar');
        $this->dispatch('avatar-updated');
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->forceFill(['avatar_path' => null])->save();
        }

        $this->dispatch('avatar-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Avatar') }}</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('A picture shown next to your posts.') }}</p>
    </header>

    <form wire:submit="updateAvatar" class="mt-6 space-y-6">
        <div class="flex items-center gap-4">
            @if ($avatar && str_starts_with((string) $avatar->getMimeType(), 'image/'))
                <img src="{{ $avatar->temporaryUrl() }}" class="h-16 w-16 rounded-full object-cover" alt="" />
            @elseif (auth()->user()->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" class="h-16 w-16 rounded-full object-cover" alt="" />
            @else
                <div class="h-16 w-16 rounded-full bg-brand-600 text-white flex items-center justify-center font-semibold">
                    {{ auth()->user()->initials() }}
                </div>
            @endif

            <input type="file" wire:model="avatar" accept="image/*" class="text-sm" />
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('avatar')" />

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (auth()->user()->avatar_path)
                <button type="button" wire:click="removeAvatar"
                    class="text-sm text-gray-500 hover:text-red-600">{{ __('Remove') }}</button>
            @endif

            <x-action-message class="me-3" on="avatar-updated">{{ __('Saved.') }}</x-action-message>
        </div>
    </form>
</section>
