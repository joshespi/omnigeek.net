<x-app-layout og-title="Series" og-description="Multi-part reads on {{ config('app.name') }}, in order from the first part.">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100 mb-6">Series</h1>

        <div class="grid gap-3">
            @forelse ($allSeries as $series)
                <a href="{{ route('series.show', $series) }}" wire:navigate
                    class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 hover:shadow transition">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $series->name }}</span>
                        <span class="text-sm text-gray-400 whitespace-nowrap">{{ $series->posts_count }} {{ Str::plural('part', $series->posts_count) }}</span>
                    </div>
                    @if ($series->description)
                        {{-- Plain-text excerpt: the Markdown is rendered in full on the series page. --}}
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $series->excerpt(180) }}
                        </p>
                    @endif
                </a>
            @empty
                <p class="text-gray-500">No series yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
