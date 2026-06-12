<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
        <div class="flex items-center gap-4">
            <x-avatar :user="$user" size="lg" />
            <div>
                <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">{{ $user->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</p>
            </div>
        </div>

        @if ($user->bio)
            <p class="mt-4 text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">{{ $user->bio }}</p>
        @endif
    </div>

    <x-posts-feed :posts="$posts" />
</div>
