<div class="site-container py-10">
    <header class="mb-7 rounded-xl border border-editorial-border bg-editorial-surface p-6 sm:p-8">
        <div class="flex items-center gap-4">
            <x-avatar :user="$user" size="lg" />
            <div>
                <h1 class="page-title [overflow-wrap:anywhere]">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-editorial-muted">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</p>
            </div>
        </div>

        @if ($user->bio)
            <p class="mt-4 max-w-2xl whitespace-pre-wrap leading-relaxed text-editorial-body [overflow-wrap:anywhere]">{{ $user->bio }}</p>
        @endif
    </header>

    <x-posts-feed :posts="$posts" />
</div>
