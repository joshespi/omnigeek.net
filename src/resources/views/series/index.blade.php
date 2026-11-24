<x-app-layout og-title="Series" og-description="Multi-part reads on {{ config('app.name') }}, in order from the first part.">
    <div class="site-container py-10">
        <h1 class="page-title mb-6">Series</h1>

        <div class="editorial-grid">
            @forelse ($allSeries as $series)
                <a href="{{ route('series.show', $series) }}" wire:navigate
                    class="editorial-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-display text-lg font-semibold text-editorial-heading [overflow-wrap:anywhere]">{{ $series->name }}</span>
                        <span class="shrink-0 text-sm text-editorial-muted">{{ $series->posts_count }} {{ Str::plural('part', $series->posts_count) }}</span>
                    </div>
                    @if ($series->description)
                        {{-- Plain-text excerpt: the Markdown is rendered in full on the series page. --}}
                        <p class="mt-2 text-sm text-editorial-body">
                            {{ $series->excerpt(180) }}
                        </p>
                    @endif
                </a>
            @empty
                <p class="editorial-empty">No series yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
