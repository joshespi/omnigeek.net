<footer class="border-t border-editorial-border bg-editorial-surface">
    <div class="site-container py-10">
        <div class="flex flex-col gap-8 md:flex-row md:justify-between">
            <div>
                <x-site-logo />
                <p class="mt-2 max-w-sm text-sm leading-relaxed text-editorial-body">
                    An independent publication about movies, television, games, books, and geek culture.
                </p>
            </div>

            <nav aria-label="Footer" class="flex flex-wrap content-start gap-x-6 gap-y-3 text-sm font-medium">
                <a class="site-link" href="{{ route('categories.index') }}" wire:navigate>Categories</a>
                <a class="site-link" href="{{ route('series.index') }}" wire:navigate>Series</a>
                <a class="site-link" href="{{ route('subscribe') }}" wire:navigate>Subscribe</a>
                <a class="site-link" href="{{ config('services.discord.invite_url') }}">Join our Discord</a>
                <a class="site-link" href="{{ route('feed') }}">RSS</a>
                <a class="site-link" href="{{ route('removal-request') }}" wire:navigate>Request a removal</a>
            </nav>
        </div>
        <p class="mt-8 border-t border-editorial-border pt-5 text-sm text-editorial-muted">
            &copy; {{ now()->year }} {{ config('app.name', 'OmniGeek') }}
        </p>
    </div>
</footer>
