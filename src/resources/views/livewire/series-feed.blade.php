<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-center gap-3">
        <svg class="w-6 h-6 shrink-0 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Series · {{ $posts->total() }} {{ Str::plural('part', $posts->total()) }}</p>
            <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">{{ $series->name }}</h1>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($posts as $post)
            <div wire:key="post-{{ $post->id }}" class="flex gap-3">
                <div class="shrink-0 mt-1 w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 flex items-center justify-center text-sm font-semibold">
                    {{ $post->series_part ?? '•' }}
                </div>
                <div class="flex-1 min-w-0">
                    @include('partials.post-card', ['post' => $post])
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500">This series has no published posts yet.</p>
        @endforelse

        <div class="mt-4">{{ $posts->links() }}</div>
    </div>
</div>
