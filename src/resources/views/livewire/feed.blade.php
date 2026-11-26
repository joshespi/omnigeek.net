<div class="site-container pb-14">
    <header class="max-w-5xl pb-4 pt-6 sm:py-12">
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

    @if ($posts->onFirstPage())
        <section aria-labelledby="discord-community" class="mx-auto mt-12 grid max-w-4xl items-center gap-5 rounded-xl border border-editorial-border bg-editorial-surface p-5 sm:p-6 md:grid-cols-[minmax(0,1fr)_minmax(0,350px)] md:gap-6">
            <div>
                <h2 id="discord-community" class="section-title">OmniGeek on Discord</h2>
                <p class="mt-3 max-w-md leading-relaxed text-editorial-body">
                    Share what you’re watching, playing, or reading, and swap recommendations with other OmniGeek readers.
                </p>
                <a href="{{ config('services.discord.invite_url') }}" class="site-primary-action mt-5 gap-2">
                    <svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.211.375-.444.864-.608 1.249a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.249.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.056 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.127c.126-.094.252-.192.372-.291a.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.099.246.198.373.292a.077.077 0 0 1-.007.126 12.299 12.299 0 0 1-1.873.891.077.077 0 0 0-.04.107c.36.698.771 1.363 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .031-.055c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028ZM8.02 15.33c-1.183 0-2.157-1.086-2.157-2.419 0-1.332.956-2.418 2.157-2.418 1.21 0 2.176 1.095 2.157 2.418 0 1.333-.956 2.419-2.157 2.419Zm7.975 0c-1.183 0-2.157-1.086-2.157-2.419 0-1.332.956-2.418 2.157-2.418 1.21 0 2.176 1.095 2.157 2.418 0 1.333-.947 2.419-2.157 2.419Z"/>
                    </svg>
                    Join our Discord
                </a>
            </div>

            <iframe src="{{ config('services.discord.widget_url') }}"
                title="OmniGeek Discord server activity" width="350" height="250" loading="lazy"
                class="block w-full max-w-[350px] rounded-lg border-0"
                sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"></iframe>
        </section>
    @endif
</div>
