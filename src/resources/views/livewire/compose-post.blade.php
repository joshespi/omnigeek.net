<div>
    <form wire:submit="save">
        <input type="text" wire:model="form.title" placeholder="Title (optional)"
            class="w-full mb-3 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm font-medium" />
        <x-input-error :messages="$errors->get('form.title')" class="mt-1 mb-2" />

        <x-markdown-editor model="form.body" :body="$form->body" :show-preview="$showPreview" :rows="12" />
        <x-input-error :messages="$errors->get('form.body')" class="mt-1" />

        <input type="text" wire:model="form.youtube" placeholder="Paste a YouTube link (optional)"
            class="w-full mt-3 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
        <x-input-error :messages="$errors->get('form.youtube')" class="mt-1" />

        @if ($categories->isNotEmpty())
            <div class="mt-3">
                <x-checkbox-pills :items="$categories" model="form.selectedCategories" />
            </div>
            <x-input-error :messages="$errors->get('form.selectedCategories')" class="mt-1" />
        @endif

        <input type="text" wire:model="form.tags" list="tag-hints-compose"
            placeholder="Add tags, space-separated (e.g. rust homelab)"
            class="w-full mt-3 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
        @if ($tagHints->isNotEmpty())
            <datalist id="tag-hints-compose">
                @foreach ($tagHints as $hint)
                    <option value="{{ $hint }}"></option>
                @endforeach
            </datalist>
            <p class="mt-1 text-xs text-gray-400">
                {{ $tagHints->take(12)->implode(', ') }}{{ $tagHints->count() > 12 ? '…' : '' }}</p>
        @endif
        <x-input-error :messages="$errors->get('form.tags')" class="mt-1" />

        <div class="mt-3">
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Post date (optional — leave blank to publish now)</label>
            <input type="datetime-local" wire:model="form.publishedAt"
                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            <x-input-error :messages="$errors->get('form.publishedAt')" class="mt-1" />
        </div>

        {{-- Reveal the NSFW option client-side; no server round-trip just to toggle a checkbox. --}}
        <div class="mt-3 space-y-2" x-data="{ toMemes: @js($form->toMemes) }">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="checkbox" wire:model="form.toMemes" x-model="toMemes"
                    class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-brand-600 focus:ring-brand-500" />
                Post to Memes instead of the main feed
            </label>

            <label x-show="toMemes" x-cloak class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer ml-6">
                <input type="checkbox" wire:model="form.nsfw"
                    class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-red-600 focus:ring-red-500" />
                Mark as NSFW (blurred until revealed)
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            <label class="text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="file" wire:model="media" accept="image/*,video/*" multiple class="text-sm" />
            </label>
            <x-primary-button wire:loading.attr="disabled" wire:target="media">Post</x-primary-button>
        </div>
        <x-input-error :messages="$errors->get('media')" class="mt-1" />
        <x-input-error :messages="$errors->get('media.*')" class="mt-1" />
        <div wire:loading wire:target="media" class="text-sm text-gray-500 mt-1">Uploading…</div>
    </form>
</div>
