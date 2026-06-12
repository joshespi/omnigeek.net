<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6">
        <input type="search" wire:model.live.debounce.300ms="query"
            placeholder="Search posts…"
            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500"
            autofocus />
    </div>

    @if ($posts === null)
        <p class="text-center text-gray-400 dark:text-gray-500 text-sm">Type at least 2 characters to search.</p>
    @elseif ($posts->isEmpty())
        <p class="text-center text-gray-400 dark:text-gray-500 text-sm">No results for <strong>{{ $query }}</strong>.</p>
    @else
        <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">{{ $posts->total() }} {{ Str::plural('result', $posts->total()) }} for <strong>{{ $query }}</strong></p>
        <x-posts-feed :posts="$posts" />
    @endif
</div>
