<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">Tag</p>
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">#{{ $tag->name }}</h1>
    </div>

    <x-posts-feed :posts="$posts" empty="No posts with this tag yet." />
</div>
