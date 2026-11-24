@props(['post', 'cover' => false, 'priority' => false])

<div @class([
        'relative bg-black',
        'editorial-cover' => $cover,
        'aspect-video overflow-hidden rounded-lg' => ! $cover,
    ]) x-data="{ play: false }">
    <button type="button" x-show="!play" x-on:click="play = true"
        class="absolute inset-0 h-full w-full" aria-label="Play YouTube video">
        <img src="https://i.ytimg.com/vi/{{ $post->youtube_id }}/hqdefault.jpg" alt=""
            loading="{{ $priority ? 'eager' : 'lazy' }}" @if ($priority) fetchpriority="high" @endif
            class="h-full w-full object-cover" />
        <x-play-icon size="lg" />
    </button>
    <template x-if="play">
        <iframe class="absolute inset-0 h-full w-full"
            src="https://www.youtube-nocookie.com/embed/{{ $post->youtube_id }}?autoplay=1&playsinline=1"
            title="YouTube video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen></iframe>
    </template>
</div>
