@props(['post', 'featured' => false, 'numbered' => false, 'heading' => 'h3', 'editable' => true])

@php
    $url = route('posts.show', $post);
    $hasCover = $post->media->isNotEmpty() || $post->youtube_id;
    $body = $post->plainBody();
    $titleLimit = 92;
    $excerptLimit = $featured ? 235 : 150;
    if (! $hasCover && ! $post->title && mb_strwidth($body) > $titleLimit) {
        $sentences = preg_split('/(?<=[.!?])\s+/u', $body, 3);
        $title = Str::limit($sentences[0], $titleLimit, '', preserveWords: true);
        $continuation = $title === $sentences[0]
            ? ($sentences[1] ?? '')
            : ltrim(Str::substr($body, Str::length($title)));
        $excerpt = Str::limit($continuation, $excerptLimit);
    } else {
        $title = $post->preview($titleLimit) ?? $post->displayTitle();
        $excerpt = $post->title ? Str::limit($body, $excerptLimit) : null;
    }
@endphp

<article {{ $attributes->class(['editorial-card', 'editorial-card--featured' => $featured && $hasCover]) }}>
    @if ($post->media->isNotEmpty())
        @include('partials.post-gallery', ['items' => $post->media, 'post' => $post, 'cover' => true, 'priority' => $featured])
    @elseif ($post->youtube_id)
        @include('partials.post-youtube', ['post' => $post, 'cover' => true, 'priority' => $featured])
    @endif

    <div @class(['flex min-w-0 grow flex-col gap-3 p-5', 'md:justify-center md:p-8 lg:p-10' => $featured, 'max-w-3xl' => $featured && ! $hasCover])>
        @if ($featured)
            <p class="editorial-eyebrow text-editorial-muted">Featured</p>
        @endif
        <x-post-categories :post="$post" />
        <{{ $heading }} @class([
            'font-display font-bold leading-tight text-editorial-heading [overflow-wrap:anywhere]',
            'text-xl' => ! $featured && $hasCover,
            'text-2xl' => ! $featured && ! $hasCover,
            'text-2xl sm:text-3xl lg:text-4xl' => $featured,
        ])>
            <a href="{{ $url }}" wire:navigate class="transition-colors hover:text-editorial-accent">
                @if ($numbered && $post->series_part) Part {{ $post->series_part }} · @endif
                {{ $title }}
            </a>
        </{{ $heading }}>
        @if ($excerpt)
            <p @class(['leading-relaxed text-editorial-body [overflow-wrap:anywhere]', 'line-clamp-3 text-sm' => $hasCover])>{{ $excerpt }}</p>
        @endif
        <x-post-byline :post="$post" :class="$featured ? '' : 'mt-auto'" />

        @if ($editable && $post->canEdit(auth()->user()))
            <livewire:post-editor :post="$post" :key="'editor-'.$post->id" />
        @endif
    </div>
</article>
