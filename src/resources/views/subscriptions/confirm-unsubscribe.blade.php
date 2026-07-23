<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-16 text-center">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100 mb-2">Unsubscribe?</h1>
        <p class="text-gray-500 dark:text-gray-400 mb-6">You'll stop getting emails from omnigeek.net.</p>
        <form method="POST" action="{{ route('subscriptions.unsubscribe.confirm', $token) }}" class="inline">
            @csrf
            <x-primary-button type="submit">Unsubscribe</x-primary-button>
        </form>
    </div>
</x-app-layout>
