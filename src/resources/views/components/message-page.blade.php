@props(['title', 'code' => null])

{{-- Centered status page (errors, etc.) inside the normal app shell. Not indexed. --}}
<x-app-layout :og-title="$title" robots="noindex,follow">
    <div class="max-w-xl mx-auto px-4 py-16 text-center">
        @if ($code)
            <p class="text-sm font-semibold text-brand-600 dark:text-brand-400">{{ $code }}</p>
        @endif
        <h1 class="mt-2 text-2xl font-display font-bold text-gray-900 dark:text-gray-100">{{ $title }}</h1>
        <p class="mt-2 text-gray-500 dark:text-gray-400">{{ $slot }}</p>
        <a href="{{ route('home') }}" wire:navigate class="inline-block mt-6 text-brand-600 dark:text-brand-400 hover:underline">Back to the feed</a>
    </div>
</x-app-layout>
