<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
        <div class="flex items-center gap-4">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" class="h-16 w-16 rounded-full object-cover" alt="" />
            @else
                <div class="h-16 w-16 rounded-full bg-brand-600 text-white text-xl flex items-center justify-center font-semibold">
                    {{ $user->initials() }}
                </div>
            @endif
            <div>
                <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">{{ $user->name }}</h1>
                @php($count = $user->posts()->count())
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $count }} {{ Str::plural('post', $count) }}</p>
            </div>
        </div>

        @if ($user->bio)
            <p class="mt-4 text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">{{ $user->bio }}</p>
        @endif
    </div>

    <div class="space-y-4">
        @forelse ($posts as $post)
            <div wire:key="post-{{ $post->id }}">
                @include('partials.post-card', ['post' => $post])
            </div>
        @empty
            <p class="text-center text-gray-500">No posts yet.</p>
        @endforelse

        <div class="mt-4">{{ $posts->links() }}</div>
    </div>
</div>
