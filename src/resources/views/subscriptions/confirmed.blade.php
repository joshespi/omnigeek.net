<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-16 text-center">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100 mb-2">Subscription confirmed</h1>
        <p class="text-gray-500 dark:text-gray-400">You'll get an email when new posts go up.</p>
        <a href="{{ route('home') }}" wire:navigate class="inline-block mt-6 text-brand-600 dark:text-brand-400 hover:underline">Back to the feed</a>
    </div>
</x-app-layout>
