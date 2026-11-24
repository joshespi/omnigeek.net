<div class="site-container py-10">
    <header class="mb-6">
        <h1 class="page-title">{{ $category->name }}</h1>
        <p class="mt-2 text-sm text-editorial-muted">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</p>
    </header>

    <x-category-filter :active="$category->slug" />
    <x-posts-feed :posts="$posts" empty="No posts in this category yet." />
</div>
