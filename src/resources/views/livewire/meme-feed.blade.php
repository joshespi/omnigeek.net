<div class="max-w-4xl mx-auto px-4 py-8"
    x-data="{ entered: localStorage.getItem('memes-nsfw-ok') === '1' }">

    {{-- NSFW gate: a solid screen that hides the feed until accepted (shown once per browser). --}}
    <div x-show="! entered" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 px-4">
        <div class="max-w-md text-center">
            <h2 class="text-lg font-semibold text-white">Content warning</h2>
            <p class="mt-2 text-sm text-gray-300">
                The memes feed may contain NSFW or otherwise sensitive content. Continue only if you're OK with that.
            </p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a href="{{ route('home') }}" wire:navigate
                    class="px-4 py-2 text-sm rounded-md bg-gray-700 text-gray-200 hover:bg-gray-600">Leave</a>
                <button type="button"
                    x-on:click="entered = true; localStorage.setItem('memes-nsfw-ok', '1')"
                    class="px-4 py-2 text-sm rounded-md bg-brand-600 text-white hover:bg-brand-700">Enter</button>
            </div>
        </div>
    </div>

    {{-- Feed stays hidden until the gate is accepted, so nothing shows through. --}}
    <div x-show="entered" x-cloak>
        <div class="mb-6">
            <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Memes</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Images and random junk — kept out of the main feed.</p>
        </div>

        <x-posts-feed :posts="$posts" :deletable="true" empty="No memes yet." />
    </div>
</div>
