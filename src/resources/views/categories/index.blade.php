<x-app-layout og-title="Categories">
    <div class="site-container py-10">
        <h1 class="page-title mb-6">Categories</h1>

        <div class="editorial-grid">
            @forelse ($categories as $category)
                <a href="{{ route('categories.show', $category) }}" wire:navigate
                    class="editorial-card flex-row items-center justify-between gap-3 p-5">
                    <span class="font-display text-lg font-semibold text-editorial-heading [overflow-wrap:anywhere]">{{ $category->name }}</span>
                    <span class="shrink-0 text-sm text-editorial-muted">{{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}</span>
                </a>
            @empty
                <p class="editorial-empty">No categories yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
