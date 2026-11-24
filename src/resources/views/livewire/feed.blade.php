<div class="site-container pb-14">
    <header class="max-w-5xl py-9 sm:py-12">
        <p class="editorial-eyebrow mb-3">Your corner of geek culture</p>
        <h1 class="text-balance font-display text-4xl font-bold leading-tight tracking-tight text-editorial-heading lg:text-[3.5rem]">Everything geeky. All in one place.</h1>
        <p class="mt-4 max-w-xl leading-relaxed text-editorial-body">Movies, television, games, books, and everything in between. Real opinions from people who love this stuff.</p>
    </header>

    <x-category-filter />

    @if ($featured)
        <x-editorial-card :post="$featured" featured heading="h2" wire:key="featured-post-{{ $featured->id }}" />
    @endif

    @if (! $featured || $posts->count() > 1 || $posts->hasPages())
        <section aria-labelledby="latest-posts" class="mt-9">
            <h2 id="latest-posts" class="section-title mb-4">Latest posts</h2>
            <x-posts-feed :posts="$posts" :skip-first="$featured !== null" heading="h3" />
        </section>
    @endif
</div>
