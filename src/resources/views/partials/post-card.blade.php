@props(['post'])

<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <x-avatar :user="$post->user" size="md" />
            <div>
                <a href="{{ route('geeks.show', $post->user) }}" wire:navigate
                    class="font-semibold text-gray-900 dark:text-gray-100 hover:underline">{{ $post->user->name }}</a>
                <a href="{{ route('posts.show', $post) }}" wire:navigate
                    class="text-gray-400 text-sm hover:underline">· {{ $post->created_at->diffForHumans() }}</a>
            </div>
        </div>
        @if ($post->user_id === auth()->id())
            <button wire:click="deletePost({{ $post->id }})"
                wire:confirm="Delete this post?"
                class="text-gray-400 hover:text-red-600 text-sm">Delete</button>
        @endif
    </div>

    @if ($post->body)
        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-words mb-2">{{ $post->body }}</p>
    @endif

    @if ($post->media_path)
        @if ($post->media_type === 'video')
            <video controls class="rounded-md w-full max-h-96 bg-black">
                <source src="{{ Storage::disk('public')->url($post->media_path) }}">
            </video>
        @else
            <img src="{{ Storage::disk('public')->url($post->media_path) }}"
                class="rounded-md w-full" alt="" />
        @endif
    @endif

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
</div>
