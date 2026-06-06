<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">Category</p>
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">{{ $category->name }}</h1>
    </div>

    <div class="space-y-4">
        @forelse ($posts as $post)
            <div wire:key="post-{{ $post->id }}">
                @include('partials.post-card', ['post' => $post])
            </div>
        @empty
            <p class="text-center text-gray-500">No posts in this category yet.</p>
        @endforelse

        <div class="mt-4">{{ $posts->links() }}</div>
    </div>
</div>
