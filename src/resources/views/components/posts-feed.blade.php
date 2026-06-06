@props(['posts', 'empty' => 'No posts yet.'])

<div class="space-y-4">
    @forelse ($posts as $post)
        <div wire:key="post-{{ $post->id }}">
            @include('partials.post-card', ['post' => $post])
        </div>
    @empty
        <p class="text-center text-gray-500">{{ $empty }}</p>
    @endforelse

    <div class="mt-4">{{ $posts->links() }}</div>
</div>
