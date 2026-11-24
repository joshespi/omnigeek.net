<div class="site-container py-10">
    <div class="mb-6 flex items-center gap-3">
        <svg class="w-6 h-6 shrink-0 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <div>
            <p class="text-sm text-editorial-muted">
                <a href="{{ route('series.index') }}" wire:navigate class="hover:underline">Series</a>
                · {{ $posts->total() }} {{ Str::plural('part', $posts->total()) }}
            </p>
            <h1 class="page-title [overflow-wrap:anywhere]">{{ $series->name }}</h1>
        </div>
    </div>

    {{-- Introduction and the two things a new reader wants: where to start, how to follow. --}}
    @if ($series->description || $first)
        <div class="mb-7 max-w-3xl rounded-xl border border-editorial-border bg-editorial-surface p-6">
            @if ($series->description)
                <x-markdown :text="$series->description" />
            @endif

            @if ($first)
                <div @class(['flex flex-wrap items-center gap-3', 'mt-4' => $series->description])>
                    <a href="{{ route('posts.show', $first) }}" wire:navigate
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-brand-600 text-white hover:bg-brand-700">
                        Start reading
                    </a>
                    <a href="{{ route('subscribe', ['series' => $series->slug]) }}" wire:navigate
                        class="text-sm text-brand-600 dark:text-brand-400 hover:underline">
                        Follow this series by email
                    </a>
                </div>
            @endif
        </div>
    @endif

    <x-posts-feed :posts="$posts" empty="This series has no published posts yet." :numbered="true" />
</div>
