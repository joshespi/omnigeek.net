@php
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title
            ?: str($plain)->limit(110)->toString()
            ?: 'A post by '.$post->user->name,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('posts.show', $post)],
        'datePublished' => $post->displayDate()->toAtomString(),
        'dateModified' => $post->updated_at->toAtomString(),
        'author' => [
            '@type' => 'Person',
            'name' => $post->user->name,
            'url' => route('geeks.show', $post->user),
        ],
    ];

    if ($plain !== '') {
        $jsonLd['articleBody'] = $plain;
    }

    if ($image) {
        $jsonLd['image'] = [$image];
    }
@endphp

<div class="site-container pb-14 pt-8 sm:pt-12">
    {{-- Must stay inside the root div — Livewire attaches wire:snapshot/wire:id to
         whatever the first element in this view is, and a preceding sibling here
         steals that instead of the intended wrapper. --}}
    {{-- HEX_TAG escapes < / > so nothing in user-authored post content can close the tag early. --}}
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

    <article class="mx-auto max-w-[48rem]">
        <header>
            <a href="{{ route('home') }}" wire:navigate class="site-link text-sm font-medium">Back to feed</a>
            <x-post-categories :post="$post" class="mt-6" />
            <h1 class="mt-3 font-display text-3xl font-bold leading-tight tracking-tight text-editorial-heading [overflow-wrap:anywhere] sm:text-5xl lg:text-[3.5rem]">
                {{ $post->displayTitle() }}
            </h1>

            <div class="mt-5 flex items-center gap-3">
                <x-avatar :user="$post->user" size="md" />
                <x-post-byline :post="$post" updated />
            </div>
            @include('partials.post-moderation', ['post' => $post, 'movable' => true])
        </header>

        @php($canEdit = $post->canEdit(auth()->user()))
        @if ($canEdit || $post->media->isNotEmpty() || $post->youtube_id)
            <div class="mt-7 space-y-4">
                @if ($canEdit)
                    <livewire:post-media-editor :post="$post" :key="'media-'.$post->id" />
                @elseif ($post->media->isNotEmpty())
                    @include('partials.post-gallery', ['items' => $post->media, 'post' => $post, 'priority' => ! $post->nsfw])
                @endif
                @if ($post->youtube_id)
                    @include('partials.post-youtube', ['post' => $post, 'priority' => $post->media->isEmpty() && ! $post->nsfw])
                @endif
            </div>
        @endif

        @if ($post->body)
            <x-markdown :text="$post->body" article class="mt-8" />
        @endif

        @if ($canEdit)
            <livewire:post-editor :post="$post" :key="'editor-'.$post->id" />
        @endif

        <footer class="mt-9 border-t border-editorial-border pt-5">
            @if ($post->tags->isNotEmpty())
                <ul aria-label="Tags" class="flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <li><a href="{{ route('tags.show', $tag) }}" wire:navigate class="category-chip">#{{ $tag->name }}</a></li>
                    @endforeach
                </ul>
            @endif

            @if ($post->view_count > 0)
                <p class="mt-3 text-sm text-editorial-muted">{{ number_format($post->view_count) }} {{ Str::plural('view', $post->view_count) }}</p>
            @endif

            @if ($series)
                @include('partials.post-series', array_merge($series, ['current' => $post]))
            @endif

            @include('partials.post-share', ['post' => $post])

            <p class="mt-4 text-sm text-editorial-muted">
                Appear in this post and would rather not?
                <a href="{{ route('removal-request', ['post' => $post->id]) }}" wire:navigate class="site-link underline">Request a removal</a>.
            </p>
        </footer>
    </article>

    @if ($related->isNotEmpty())
        <section aria-labelledby="related-posts" class="mt-12 border-t border-editorial-border pt-7">
            <h2 id="related-posts" class="section-title mb-5">Related posts</h2>
            <div class="editorial-grid">
                @foreach ($related as $relatedPost)
                    <x-editorial-card :post="$relatedPost" :editable="false" wire:key="related-{{ $relatedPost->id }}" />
                @endforeach
            </div>
        </section>
    @endif
</div>
