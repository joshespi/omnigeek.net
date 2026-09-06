@php
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title
            ?: str(strip_tags($post->body ?? ''))->limit(110)->toString()
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

    if ($post->body) {
        $jsonLd['articleBody'] = strip_tags($post->body);
    }

    if ($image) {
        $jsonLd['image'] = [$image];
    }
@endphp

<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Must stay inside the root div — Livewire attaches wire:snapshot/wire:id to
         whatever the first element in this view is, and a preceding sibling here
         steals that instead of the intended wrapper. --}}
    {{-- HEX_TAG escapes < / > so nothing in user-authored post content can close the tag early. --}}
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

    <a href="{{ route('home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-500">← Back to feed</a>

    <div class="mt-4">
        @include('partials.post-card', ['post' => $post, 'full' => true])
    </div>

    @if ($series)
        @include('partials.post-series', array_merge($series, ['current' => $post]))
    @endif

    @include('partials.post-share', ['post' => $post])
</div>
