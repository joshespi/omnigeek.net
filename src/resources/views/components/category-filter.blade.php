@props(['active' => null])

<nav aria-label="Filter by category" class="mb-7 flex gap-2 overflow-x-auto p-1">
    <a href="{{ route('home') }}" wire:navigate @if ($active === null) aria-current="page" @endif
        class="category-chip">All posts</a>
    @foreach (\App\Models\Category::forNavigation() as $category)
        <a href="{{ route('categories.show', $category) }}" wire:navigate
            @if ($active === $category->slug) aria-current="page" @endif class="category-chip">
            {{ $category->name }}
        </a>
    @endforeach
</nav>
