<div class="site-container py-10">
    <div class="mb-6">
        <p class="text-sm text-editorial-muted">Tag</p>
        <h1 class="page-title [overflow-wrap:anywhere]">#{{ $tag->name }}</h1>
    </div>

    <x-posts-feed :posts="$posts" empty="No posts with this tag yet." />
</div>
