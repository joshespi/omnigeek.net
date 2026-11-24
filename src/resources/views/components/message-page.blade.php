@props(['title', 'code' => null])

{{-- Centered status page (errors, etc.) inside the normal app shell. Not indexed. --}}
<x-app-layout :og-title="$title" robots="noindex,follow">
    <div class="site-container max-w-xl py-16 text-center">
        @if ($code)
            <p class="editorial-eyebrow">{{ $code }}</p>
        @endif
        <h1 class="page-title mt-2">{{ $title }}</h1>
        <p class="mt-2 text-editorial-body">{{ $slot }}</p>
        <a href="{{ route('home') }}" wire:navigate class="inline-block mt-6 text-brand-600 dark:text-brand-400 hover:underline">Back to the feed</a>
    </div>
</x-app-layout>
