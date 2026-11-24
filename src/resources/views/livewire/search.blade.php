<div class="site-container py-10">
    <div class="mb-6 max-w-xl">
        <h1 class="page-title mb-5">Search</h1>
        <label for="post-search" class="sr-only">Search posts</label>
        <input id="post-search" type="search" wire:model.live.debounce.300ms="query"
            placeholder="Search by title, text, or author"
            class="w-full rounded-lg border-editorial-border bg-editorial-surface px-4 py-3 text-editorial-heading placeholder:text-editorial-muted focus:border-editorial-accent focus:ring-editorial-accent"
            autofocus />
    </div>

    <p wire:offline class="editorial-empty mb-4">Search is unavailable offline.</p>
    <div role="status" aria-atomic="true">
        <p wire:loading.delay wire:target="query" class="mb-4 text-sm text-editorial-muted">Searching…</p>
        <div wire:loading.remove wire:target="query">
            @if ($posts === null)
                <p class="editorial-empty">Type at least 3 characters to search.</p>
            @elseif ($posts->isEmpty())
                <p class="editorial-empty [overflow-wrap:anywhere]">No results for <strong>{{ $query }}</strong>.</p>
            @else
                <p class="mb-4 text-sm text-editorial-muted [overflow-wrap:anywhere]">{{ $posts->total() }} {{ Str::plural('result', $posts->total()) }} for <strong>{{ $query }}</strong></p>
            @endif
        </div>
    </div>
    @if ($posts && $posts->isNotEmpty())
        <x-posts-feed :posts="$posts" />
    @endif
</div>
