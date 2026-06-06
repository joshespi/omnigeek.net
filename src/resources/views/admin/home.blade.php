<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100 mb-6">Admin</h1>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('admin.categories') }}" wire:navigate
                class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:shadow transition">
                <div class="font-medium text-gray-900 dark:text-gray-100">Categories</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage the fixed category list posters choose from.</p>
            </a>
        </div>
    </div>
</x-app-layout>
