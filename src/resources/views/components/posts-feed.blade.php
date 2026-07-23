@props(['posts', 'empty' => 'No posts yet.', 'deletable' => false, 'numbered' => false])

<div class="space-y-4">
    @forelse ($posts as $post)
        <div wire:key="post-{{ $post->id }}" @class(['flex gap-3' => $numbered])>
            @if ($numbered)
                <div class="shrink-0 mt-1 w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 flex items-center justify-center text-sm font-semibold">
                    {{ $post->series_part ?? '•' }}
                </div>
                <div class="flex-1 min-w-0">
                    @include('partials.post-card', ['post' => $post, 'deletable' => $deletable])
                </div>
            @else
                @include('partials.post-card', ['post' => $post, 'deletable' => $deletable])
            @endif
        </div>
    @empty
        <p class="text-center text-gray-500">{{ $empty }}</p>
    @endforelse

    <div class="mt-4">{{ $posts->links() }}</div>
</div>
