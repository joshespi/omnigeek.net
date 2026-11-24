@props(['post'])

@if ($post->categories->isNotEmpty())
    <div {{ $attributes->class('flex flex-wrap gap-x-3 gap-y-1 text-sm font-semibold text-editorial-accent') }}>
        @foreach ($post->categories as $category)
            <a href="{{ route('categories.show', $category) }}" wire:navigate class="hover:underline [overflow-wrap:anywhere]">{{ $category->name }}</a>
        @endforeach
    </div>
@endif
