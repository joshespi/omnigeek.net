<div class="max-w-3xl mx-auto px-4 py-8">
    @auth
        <form wire:submit="save" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6">
            <input type="text" wire:model="title" placeholder="Title (optional)"
                class="w-full mb-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm font-medium" />
            <x-input-error :messages="$errors->get('title')" class="mt-1" />

            <textarea wire:model="body" rows="3" placeholder="What's on your mind?"
                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 resize-none"></textarea>
            <x-input-error :messages="$errors->get('body')" class="mt-1" />

            <input type="text" wire:model="youtube" placeholder="Paste a YouTube link (optional)"
                class="w-full mt-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            <x-input-error :messages="$errors->get('youtube')" class="mt-1" />

            @if ($categories->isNotEmpty())
                <div class="mt-3">
                    <x-checkbox-pills :items="$categories" model="selectedCategories" />
                </div>
                <x-input-error :messages="$errors->get('selectedCategories')" class="mt-1" />
            @endif

            <input type="text" wire:model="tags" list="tag-hints" placeholder="Add tags, space-separated (e.g. rust homelab)"
                class="w-full mt-3 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            @if ($tagHints->isNotEmpty())
                <datalist id="tag-hints">
                    @foreach ($tagHints as $hint)
                        <option value="{{ $hint }}"></option>
                    @endforeach
                </datalist>
                <p class="mt-1 text-xs text-gray-400">Existing tags: {{ $tagHints->take(12)->implode(', ') }}{{ $tagHints->count() > 12 ? '…' : '' }}</p>
            @endif
            <x-input-error :messages="$errors->get('tags')" class="mt-1" />

            <div class="flex items-center justify-between mt-3">
                <label class="text-sm text-gray-600 cursor-pointer">
                    <input type="file" wire:model="media" accept="image/*,video/*" class="text-sm" />
                </label>
                <x-primary-button>{{ __('Post') }}</x-primary-button>
            </div>
            <x-input-error :messages="$errors->get('media')" class="mt-1" />

            <div wire:loading wire:target="media" class="text-sm text-gray-500 mt-1">Uploading…</div>
        </form>
    @else
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6 text-center text-sm text-gray-600 dark:text-gray-300">
            <a href="{{ route('login') }}" wire:navigate class="text-brand-600 dark:text-brand-400 hover:text-brand-500 font-medium">Log in</a>
            to post. Registration is invite-only.
        </div>
    @endauth

    <x-posts-feed :posts="$posts" />
</div>
