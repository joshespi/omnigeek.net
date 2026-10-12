@props(['post', 'full' => false, 'deletable' => null])

@php
    // Delete button shows on full post pages and anywhere explicitly marked deletable
    // (e.g. the memes feed), without forcing the rest of the "full" layout.
    $showDelete = $deletable ?? $full;
@endphp

<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <x-avatar :user="$post->user" size="md" />
            <div>
                <a href="{{ route('geeks.show', $post->user) }}" wire:navigate
                    class="font-semibold text-gray-900 dark:text-gray-100 hover:underline">{{ $post->user->name }}</a>
                <a href="{{ route('posts.show', $post) }}" wire:navigate
                    class="text-gray-400 text-sm hover:underline">· {{ $post->displayDate()->diffForHumans() }}</a>
            </div>
        </div>
        @if ($showDelete && $post->canDelete(auth()->user()))
            <div class="flex items-center gap-3 text-sm">
                @if ($full)
                    @php $moveTarget = $post->oppositeFeed()->label(); @endphp
                    <button wire:click="moveToOtherFeed({{ $post->id }})"
                        wire:confirm="Move this post to the {{ $moveTarget }}?"
                        class="text-gray-400 hover:text-brand-600">Move to {{ $moveTarget }}</button>
                @endif
                <button wire:click="deletePost({{ $post->id }})"
                    wire:confirm="Delete this post?"
                    class="text-gray-400 hover:text-red-600">Delete</button>
            </div>
        @endif
    </div>

    @if ($post->title)
        <a href="{{ route('posts.show', $post) }}" wire:navigate
            class="block text-lg font-semibold text-gray-900 dark:text-gray-100 hover:underline mb-1">{{ $post->title }}</a>
    @endif

    @if ($post->body)
        @php $long = !$full && mb_strlen($post->body) > 250; @endphp
        <div class="mb-2 {{ $long ? 'line-clamp-4 overflow-hidden' : '' }}">
            <x-markdown :text="$post->body" />
        </div>
        @if ($long)
            <a href="{{ route('posts.show', $post) }}" wire:navigate
                class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Read more</a>
        @endif
    @endif

    @php $canEdit = $post->canEdit(auth()->user()); @endphp
    {{-- Livewire components only where they do something: the owner's editors. Everyone
         else gets the plain gallery, saving ~2 component mounts + snapshots per card. --}}
    @if ($full && $canEdit)
        <livewire:post-media-editor :post="$post" :key="'media-'.$post->id" />
    @elseif ($post->media->isNotEmpty())
        @include('partials.post-gallery', ['items' => $post->media, 'post' => $post])
    @endif
    @if ($canEdit)
        <livewire:post-editor :post="$post" :key="'editor-'.$post->id" />
    @endif

    @if ($post->youtube_id)
        {{-- Click-to-load: a thumbnail until played, so a feed of videos doesn't pull
             an iframe + YouTube's scripts per card. youtube-nocookie avoids tracking cookies. --}}
        <div class="aspect-video mt-2 relative rounded-md overflow-hidden bg-black" x-data="{ play: false }">
            <button type="button" x-show="! play" x-on:click="play = true"
                class="absolute inset-0 w-full h-full" aria-label="Play YouTube video">
                <img src="https://i.ytimg.com/vi/{{ $post->youtube_id }}/hqdefault.jpg" alt="" loading="lazy"
                    class="w-full h-full object-cover" />
                <x-play-icon size="lg" />
            </button>
            <template x-if="play">
                <iframe class="w-full h-full"
                    src="https://www.youtube-nocookie.com/embed/{{ $post->youtube_id }}?autoplay=1&playsinline=1"
                    title="YouTube video" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
            </template>
        </div>
    @endif

    @if ($post->categories->isNotEmpty())
        <div class="flex flex-wrap gap-2 mt-3">
            @foreach ($post->categories as $category)
                <a href="{{ route('categories.show', $category) }}" wire:navigate
                    class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if ($post->tags->isNotEmpty())
        <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2">
            @foreach ($post->tags as $tag)
                <a href="{{ route('tags.show', $tag) }}" wire:navigate
                    class="text-xs text-brand-600 dark:text-brand-400 hover:underline">#{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif

    @if ($full && $post->view_count > 0)
        <div class="mt-3 text-xs text-gray-400 dark:text-gray-500">{{ number_format($post->view_count) }} {{ Str::plural('view', $post->view_count) }}</div>
    @endif
</div>
