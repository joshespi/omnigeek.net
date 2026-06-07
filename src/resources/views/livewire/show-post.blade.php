<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-500">← Back to feed</a>

    <div class="mt-4">
        @include('partials.post-card', ['post' => $post, 'full' => true])
    </div>

    <div class="mt-4" x-data="{ copied: false }">
        <button type="button"
            x-on:click="navigator.clipboard.writeText('{{ route('posts.show', $post) }}'); copied = true; setTimeout(() => copied = false, 1500)"
            class="text-sm text-gray-500 hover:text-gray-700">
            <span x-show="! copied">Copy link to this post</span>
            <span x-show="copied" x-cloak class="text-green-600">Copied!</span>
        </button>
    </div>
</div>
