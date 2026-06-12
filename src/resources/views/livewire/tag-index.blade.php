<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Tags</h1>
        @if ($selected)
            <button wire:click="clear" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear filter</button>
        @endif
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Pick tags to filter the feed. Posts matching <strong>any</strong> selected tag are shown.</p>

    <div class="flex flex-wrap gap-2 mb-6">
        @forelse ($tags as $tag)
            @php($active = in_array($tag->slug, $selected, true))
            <button wire:click="toggle('{{ $tag->slug }}')" @class([
                'px-3 py-1 rounded-full text-sm border',
                'bg-brand-600 text-white border-brand-600' => $active,
                'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => ! $active,
            ])>
                #{{ $tag->name }} <span class="opacity-60">{{ $tag->posts_count }}</span>
            </button>
        @empty
            <p class="text-gray-500">No tags yet.</p>
        @endforelse
    </div>

    @if ($posts)
        <x-posts-feed :posts="$posts" empty="No posts match the selected tags." />
    @endif
</div>
