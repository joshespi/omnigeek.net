@props(['post'])

<article class="rounded-xl border border-editorial-border bg-editorial-surface p-5">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <x-avatar :user="$post->user" size="md" />
            <x-post-byline :post="$post" />
        </div>
        @include('partials.post-moderation', ['post' => $post])
    </div>

    @if ($post->title)
        <h2 class="mb-2 font-display text-xl font-bold text-editorial-heading [overflow-wrap:anywhere]">
            <a href="{{ route('posts.show', $post) }}" wire:navigate class="hover:text-editorial-accent">{{ $post->title }}</a>
        </h2>
    @endif

    @if ($post->body)
        <x-markdown :text="$post->body" @class(['line-clamp-4' => mb_strlen($post->body) > 250]) />
    @endif

    @if ($post->media->isNotEmpty())
        @include('partials.post-gallery', ['items' => $post->media, 'post' => $post])
    @endif

    @if ($post->youtube_id)
        <div class="mt-2">
            @include('partials.post-youtube', ['post' => $post])
        </div>
    @endif

    <x-post-categories :post="$post" class="mt-3" />
    @if ($post->tags->isNotEmpty())
        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1">
            @foreach ($post->tags as $tag)
                <a href="{{ route('tags.show', $tag) }}" wire:navigate class="text-sm text-editorial-accent hover:underline">#{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif

    <a href="{{ route('posts.show', $post) }}" wire:navigate class="mt-3 inline-block text-sm text-editorial-accent hover:underline">Open post</a>

    @if ($post->canEdit(auth()->user()))
        <livewire:post-editor :post="$post" :key="'editor-'.$post->id" />
    @endif
</article>
