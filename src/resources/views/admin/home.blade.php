<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100 mb-6">Admin</h1>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('admin.media') }}" wire:navigate
                class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:shadow transition">
                <div class="font-medium text-gray-900 dark:text-gray-100">Media</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload site logo and default OG image.</p>
            </a>
            <a href="{{ route('admin.categories') }}" wire:navigate
                class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:shadow transition">
                <div class="font-medium text-gray-900 dark:text-gray-100">Categories</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage the fixed category list posters choose from.</p>
            </a>
            <a href="{{ route('admin.posts') }}" wire:navigate
                class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:shadow transition">
                <div class="font-medium text-gray-900 dark:text-gray-100">Posts</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Edit or delete any post on the site.</p>
            </a>
            <a href="{{ route('admin.series') }}" wire:navigate
                class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:shadow transition">
                <div class="font-medium text-gray-900 dark:text-gray-100">Series</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Rename a series and write the introduction shown on its page.</p>
            </a>
            @php $openRemovals = \App\Models\RemovalRequest::open()->count(); @endphp
            <a href="{{ route('admin.removal-requests') }}" wire:navigate
                class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:shadow transition">
                <div class="font-medium text-gray-900 dark:text-gray-100">
                    Removal requests
                    @if ($openRemovals > 0)
                        <span class="ml-1 inline-block rounded-full bg-red-100 dark:bg-red-900/40 px-2 py-0.5 text-xs text-red-700 dark:text-red-300">{{ $openRemovals }} open</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">People asking for a name, a quote or an image to be removed or changed.</p>
            </a>
            <a href="{{ route('admin.users') }}" wire:navigate
                class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:shadow transition">
                <div class="font-medium text-gray-900 dark:text-gray-100">Users</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage accounts, send invite links, toggle admin.</p>
            </a>
            <a href="{{ route('admin.activity') }}" wire:navigate
                class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:shadow transition">
                <div class="font-medium text-gray-900 dark:text-gray-100">Activity Log</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Audit trail of admin actions across posts, users, and categories.</p>
            </a>
        </div>
    </div>
</x-app-layout>
