@props(['post', 'linkEdits' => false])

@php
    $publishedAt = $post->displayDate();
    // Post page loads the full edits relation; cards only carry edits_count.
    $latestEdit = $post->relationLoaded('edits') ? $post->edits->first() : null;
    $edited = $post->wasEdited();
@endphp

<div {{ $attributes->class('flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-editorial-muted') }}>
    <a href="{{ route('geeks.show', $post->user) }}" wire:navigate class="site-link font-medium [overflow-wrap:anywhere]">{{ $post->user->name }}</a>
    <time datetime="{{ $publishedAt->toAtomString() }}">{{ $publishedAt->format('M j, Y') }}</time>
    @if ($edited)
        @if ($linkEdits && $latestEdit)
            <a href="#edits" class="edited-badge">Edited <time datetime="{{ $latestEdit->edited_at->toAtomString() }}">{{ $latestEdit->edited_at->format('M j, Y') }}</time></a>
        @else
            <span class="edited-badge">Edited</span>
        @endif
    @endif
</div>
