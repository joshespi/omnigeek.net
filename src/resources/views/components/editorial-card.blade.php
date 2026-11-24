@props(['post', 'featured' => false, 'numbered' => false, 'heading' => 'h3', 'editable' => true])

@php
    $url = route('posts.show', $post);
    $title = $post->preview(92) ?? $post->displayTitle();
    $excerpt = $post->title ? Str::limit($post->plainBody(), $featured ? 235 : 150) : null;
@endphp

<article {{ $attributes->class(['editorial-card', 'editorial-card--featured' => $featured]) }}>
    @if ($post->media->isNotEmpty())
        @include('partials.post-gallery', ['items' => $post->media, 'post' => $post, 'cover' => true, 'priority' => $featured])
    @elseif ($post->youtube_id)
        @include('partials.post-youtube', ['post' => $post, 'cover' => true, 'priority' => $featured])
    @else
        <a href="{{ $url }}" wire:navigate tabindex="-1" aria-hidden="true"
            class="editorial-cover flex items-center justify-center">
            <svg aria-hidden="true" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"
                class="h-11 w-11 text-editorial-accent opacity-50">
                <path d="M8 5h12l5 5v17H8zM20 5v5h5M12 15h9m-9 5h9" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
    @endif

    <div @class(['flex min-w-0 flex-col gap-3 p-5', 'md:justify-center md:p-8 lg:p-10' => $featured])>
        @if ($featured)
            <p class="editorial-eyebrow text-editorial-muted">Featured</p>
        @endif
        <x-post-categories :post="$post" />
        <{{ $heading }} @class([
            'font-display font-bold leading-tight text-editorial-heading [overflow-wrap:anywhere]',
            'text-xl' => ! $featured,
            'text-2xl sm:text-3xl lg:text-4xl' => $featured,
        ])>
            <a href="{{ $url }}" wire:navigate class="transition-colors hover:text-editorial-accent">
                @if ($numbered && $post->series_part) Part {{ $post->series_part }} · @endif
                {{ $title }}
            </a>
        </{{ $heading }}>
        @if ($excerpt)
            <p class="line-clamp-3 text-sm leading-relaxed text-editorial-body [overflow-wrap:anywhere]">{{ $excerpt }}</p>
        @endif
        <x-post-byline :post="$post" :class="$featured ? '' : 'mt-auto'" />

        @if ($editable && $post->canEdit(auth()->user()))
            <livewire:post-editor :post="$post" :key="'editor-'.$post->id" />
        @endif
    </div>
</article>
