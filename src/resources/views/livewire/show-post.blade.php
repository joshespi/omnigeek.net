<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-500">← Back to feed</a>

    <div class="mt-4">
        @include('partials.post-card', ['post' => $post, 'full' => true])
    </div>

    @include('partials.post-share', ['post' => $post])
</div>
