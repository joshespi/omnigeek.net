<div class="site-container py-10">
    <div class="flex items-center justify-between mb-4">
        <h1 class="page-title">Tags</h1>
        @if ($selected)
            <button type="button" wire:click="clear" class="text-sm text-editorial-muted hover:underline">Clear filter</button>
        @endif
    </div>

    <p class="text-sm text-editorial-muted mb-3">Pick tags to filter the feed. Posts matching <strong>any</strong> selected tag are shown.</p>

    <div class="flex flex-wrap gap-2 mb-6">
        @forelse ($tags as $tag)
            @php($active = in_array($tag->slug, $selected, true))
            <button type="button" wire:click="toggle('{{ $tag->slug }}')" aria-pressed="{{ $active ? 'true' : 'false' }}" class="category-chip gap-2">
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
