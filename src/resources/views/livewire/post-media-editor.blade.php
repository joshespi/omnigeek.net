<div>
    @if ($post->media_path)
        @if ($post->media_type === 'video')
            <video controls class="rounded-md w-full max-h-96 bg-black mt-2">
                <source src="{{ Storage::disk('public')->url($post->media_path) }}">
            </video>
        @else
            <img src="{{ Storage::disk('public')->url($post->media_path) }}"
                class="rounded-md w-full mt-2" alt="" />
        @endif
    @endif

    @if ($editContext && $post->canEdit(auth()->user()))
        @if ($editing)
            <form wire:submit="replaceMedia" class="mt-2 flex items-center gap-2">
                <input type="file" wire:model="replacement" accept="image/*,video/*" class="text-xs text-gray-600 dark:text-gray-300" />
                <button type="submit" wire:loading.attr="disabled" wire:target="replacement"
                    class="text-xs text-brand-600 dark:text-brand-400 hover:underline disabled:opacity-40 disabled:cursor-not-allowed">Save</button>
                <button type="button" wire:click="$set('editing', false)" class="text-xs text-gray-400 hover:underline">Cancel</button>
                @if ($post->media_path)
                    <button type="button" wire:click="removeMedia" wire:confirm="Remove this media?"
                        class="text-xs text-red-500 hover:underline ml-auto">Remove</button>
                @endif
            </form>
            <div wire:loading wire:target="replacement" class="text-xs text-gray-400 mt-1">Uploading…</div>
            <x-input-error :messages="$errors->get('replacement')" class="mt-1" />
        @else
            <button type="button" wire:click="$set('editing', true)"
                class="mt-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                {{ $post->media_path ? 'Replace media' : 'Add media' }}
            </button>
        @endif
    @endif
</div>
