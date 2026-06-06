<div class="max-w-3xl mx-auto px-4 py-8">
    @auth
        <form wire:submit="save" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6">
            <textarea wire:model="body" rows="3" placeholder="What's happening?"
                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 resize-none"></textarea>
            <x-input-error :messages="$errors->get('body')" class="mt-1" />

            <input type="text" wire:model="youtube" placeholder="Paste a YouTube link (optional)"
                class="w-full mt-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            <x-input-error :messages="$errors->get('youtube')" class="mt-1" />

            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach ($categories as $category)
                        <label class="cursor-pointer">
                            <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="peer sr-only" />
                            <span class="inline-block px-3 py-1 rounded-full text-sm border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-600">
                                {{ $category->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('selectedCategories')" class="mt-1" />
            @endif

            <div class="flex items-center justify-between mt-3">
                <label class="text-sm text-gray-600 cursor-pointer">
                    <input type="file" wire:model="media" accept="image/*,video/*" class="text-sm" />
                </label>
                <x-primary-button>{{ __('Post') }}</x-primary-button>
            </div>
            <x-input-error :messages="$errors->get('media')" class="mt-1" />

            <div wire:loading wire:target="media" class="text-sm text-gray-500 mt-1">Uploading…</div>
        </form>
    @else
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6 text-center text-sm text-gray-600 dark:text-gray-300">
            <a href="{{ route('login') }}" wire:navigate class="text-brand-600 dark:text-brand-400 hover:text-brand-500 font-medium">Log in</a>
            to post. Registration is invite-only.
        </div>
    @endauth

    <div class="space-y-4">
        @forelse ($posts as $post)
            <div wire:key="post-{{ $post->id }}">
                @include('partials.post-card', ['post' => $post])
            </div>
        @empty
            <p class="text-center text-gray-500">No posts yet.</p>
        @endforelse

        <div class="mt-4">{{ $posts->links() }}</div>
    </div>
</div>
