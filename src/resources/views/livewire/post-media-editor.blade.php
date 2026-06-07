<div>
    {{-- Gallery --}}
    @if ($this->items->isNotEmpty())
        @include('partials.post-gallery', ['items' => $this->items, 'editContext' => $editContext, 'post' => $post])
    @endif

    {{-- Upload form (edit context only) --}}
    @if ($editContext && $post->canEdit(auth()->user()))
        <form wire:submit="addMedia" class="mt-3">
            <div class="flex items-center gap-2">
                <input type="file" wire:model="uploads" multiple
                    accept="image/*,video/*"
                    class="text-xs text-gray-600 dark:text-gray-300 flex-1" />
                <button type="submit"
                    wire:loading.attr="disabled" wire:target="uploads,addMedia"
                    class="text-xs text-brand-600 dark:text-brand-400 hover:underline disabled:opacity-40 disabled:cursor-not-allowed">
                    Add
                </button>
            </div>
            <div wire:loading wire:target="uploads" class="text-xs text-gray-400 mt-1">Uploading…</div>
            <x-input-error :messages="$errors->get('uploads')" class="mt-1" />
            <x-input-error :messages="$errors->get('uploads.*')" class="mt-1" />
        </form>
    @endif
</div>
