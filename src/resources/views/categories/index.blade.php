<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100 mb-6">Categories</h1>

        <div class="grid gap-3 sm:grid-cols-2">
            @forelse ($categories as $category)
                <a href="{{ route('categories.show', $category) }}" wire:navigate
                    class="flex items-center justify-between bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 hover:shadow transition">
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</span>
                    <span class="text-sm text-gray-400">{{ $category->posts_count }}</span>
                </a>
            @empty
                <p class="text-gray-500">No categories yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
