@props(['post', 'updated' => false])

@php($publishedAt = $post->displayDate())

<div {{ $attributes->class('flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-editorial-muted') }}>
    <a href="{{ route('geeks.show', $post->user) }}" wire:navigate class="site-link font-medium [overflow-wrap:anywhere]">{{ $post->user->name }}</a>
    <time datetime="{{ $publishedAt->toAtomString() }}">{{ $publishedAt->format('M j, Y') }}</time>
    @if ($updated && $post->updated_at?->gt($publishedAt) && ! $post->updated_at->isSameDay($publishedAt))
        <span>Updated <time datetime="{{ $post->updated_at->toAtomString() }}">{{ $post->updated_at->format('M j, Y') }}</time></span>
    @endif
</div>
