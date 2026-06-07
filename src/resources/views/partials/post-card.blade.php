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
            <button wire:click="deletePost({{ $post->id }})"
                wire:confirm="Delete this post?"
                class="text-gray-400 hover:text-red-600 text-sm">Delete</button>
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

    <livewire:post-media-editor :post="$post" :edit-context="$full" :key="'media-'.$post->id" />
    <livewire:post-editor :post="$post" :key="'editor-'.$post->id" />

    @if ($post->youtube_id)
        <div class="aspect-video mt-2">
            <iframe class="w-full h-full rounded-md"
                src="https://www.youtube.com/embed/{{ $post->youtube_id }}"
                title="YouTube video" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
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
