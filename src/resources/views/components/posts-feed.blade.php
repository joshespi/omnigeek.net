@props(['posts', 'empty' => 'No posts yet.', 'deletable' => false, 'numbered' => false, 'skipFirst' => false, 'heading' => 'h2'])

<div>
    <div @class(['space-y-4' => $deletable, 'editorial-grid' => ! $deletable])>
        @forelse (($skipFirst ? $posts->getCollection()->skip(1) : $posts) as $post)
            @if ($deletable)
                <div wire:key="post-{{ $post->id }}">
                    @include('partials.post-card', ['post' => $post])
                </div>
            @else
                <x-editorial-card :post="$post" :numbered="$numbered" :heading="$heading" wire:key="post-{{ $post->id }}" />
            @endif
        @empty
            @unless ($skipFirst)
                <p class="editorial-empty">{{ $empty }}</p>
            @endunless
        @endforelse
    </div>

    @if ($posts->hasPages())
        <div class="mt-8">{{ $posts->onEachSide(1)->links('partials.editorial-pagination') }}</div>
    @endif
</div>
