<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Memes</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Images and random junk — kept out of the main feed.</p>
    </div>

    <x-posts-feed :posts="$posts" :deletable="true" empty="No memes yet." />
</div>
