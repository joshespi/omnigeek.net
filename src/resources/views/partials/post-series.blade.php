@props(['series', 'posts', 'position', 'total', 'prev', 'next', 'current'])

@php
    // A short label for a post in the list: its title, else "Part N", else a body snippet.
    $label = fn ($p) => $p->title ?: ($p->series_part ? 'Part '.$p->series_part : $p->preview(60) ?? 'Untitled');
@endphp

<div
    x-data="{ open: false }"
    class="mt-6 rounded-lg border border-brand-200 dark:border-brand-900 bg-brand-50/60 dark:bg-brand-950/30 p-4"
>
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('series.show', $series) }}" wire:navigate
            class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 dark:text-brand-300 hover:underline">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>{{ $series->name }}</span>
        </a>
        <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">Part {{ $position }} of {{ $total }}</span>
    </div>

    {{-- Prev / next walk --}}
    <div class="flex items-stretch gap-2 mt-3">
        @if ($prev)
            <a href="{{ route('posts.show', $prev) }}" wire:navigate
                class="flex-1 group rounded-md bg-white dark:bg-gray-800 px-3 py-2 hover:ring-1 hover:ring-brand-300 dark:hover:ring-brand-700">
                <div class="text-[11px] uppercase tracking-wide text-gray-400">‹ Previous</div>
                <div class="text-sm text-gray-700 dark:text-gray-200 truncate group-hover:text-brand-700 dark:group-hover:text-brand-300">{{ $label($prev) }}</div>
            </a>
        @else
            <div class="flex-1"></div>
        @endif

        @if ($next)
            <a href="{{ route('posts.show', $next) }}" wire:navigate
                class="flex-1 group rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-right hover:ring-1 hover:ring-brand-300 dark:hover:ring-brand-700">
                <div class="text-[11px] uppercase tracking-wide text-gray-400">Next ›</div>
                <div class="text-sm text-gray-700 dark:text-gray-200 truncate group-hover:text-brand-700 dark:group-hover:text-brand-300">{{ $label($next) }}</div>
            </a>
        @else
            <div class="flex-1"></div>
        @endif
    </div>

    {{-- Full list, collapsed by default --}}
    @if ($total > 2)
        <button type="button" x-on:click="open = !open"
            class="mt-3 text-xs text-brand-600 dark:text-brand-400 hover:underline">
            <span x-show="!open">Show all {{ $total }} parts</span>
            <span x-show="open" x-cloak>Hide parts</span>
        </button>

        <ol x-show="open" x-cloak class="mt-2 space-y-1 list-none">
            @foreach ($posts as $p)
                <li>
                    @if ($p->id === $current->id)
                        <span class="flex items-center gap-2 text-sm font-medium text-brand-700 dark:text-brand-300">
                            <span class="w-5 text-right text-gray-400">{{ $p->series_part ?? $loop->iteration }}.</span>
                            {{ $label($p) }} <span class="text-xs text-gray-400">— you’re here</span>
                        </span>
                    @else
                        <a href="{{ route('posts.show', $p) }}" wire:navigate
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 hover:text-brand-700 dark:hover:text-brand-300">
                            <span class="w-5 text-right text-gray-400">{{ $p->series_part ?? $loop->iteration }}.</span>
                            {{ $label($p) }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</div>
