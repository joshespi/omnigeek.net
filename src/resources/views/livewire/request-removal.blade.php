<div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100 mb-2">Request a removal</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        If your name, your words or an image of you appears in a post here and you would rather it didn't,
        tell us. We can blur a name, change it, or take the content down.
    </p>

    @if ($done)
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 text-center">
            <p class="text-gray-800 dark:text-gray-200">Thank you. Your request has been sent and someone will look at it.</p>
            <a href="{{ route('home') }}" wire:navigate class="mt-3 inline-block text-sm text-brand-600 dark:text-brand-400 hover:underline">← Back to feed</a>
        </div>
    @else
        <form wire:submit="save" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-5">
            @if ($post)
                <div class="rounded-md bg-gray-50 dark:bg-gray-700/50 px-3 py-2 text-sm text-gray-600 dark:text-gray-300">
                    About:
                    <a href="{{ route('posts.show', $post) }}" wire:navigate class="font-medium text-brand-600 dark:text-brand-400 hover:underline">
                        {{ $post->preview(80) ?? 'a post' }}
                    </a>
                </div>
            @endif

            <div>
                <x-input-label :value="__('What is this about?')" />
                <div class="mt-2 space-y-2 text-sm">
                    @foreach ($kinds as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="kind" value="{{ $value }}" class="text-brand-600 focus:ring-brand-500" />
                            <span class="text-gray-700 dark:text-gray-300">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('kind')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="removal-details" :value="__('What should change?')" />
                <textarea wire:model="details" id="removal-details" rows="5"
                    placeholder="Which post or image, the name as it appears, and what you would like done."
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm"></textarea>
                <x-input-error :messages="$errors->get('details')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="removal-name" :value="__('Your name (optional)')" />
                <x-text-input wire:model="name" id="removal-name" type="text" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="removal-email" :value="__('Email (optional)')" />
                <x-text-input wire:model="email" id="removal-email" type="email" class="mt-1 block w-full" placeholder="you@example.com" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Only used to reply to this request.</p>
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            {{-- Honeypot: off-screen and skipped by keyboards and screen readers. --}}
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="removal-website">Website</label>
                <input wire:model="website" id="removal-website" type="text" tabindex="-1" autocomplete="off" />
            </div>

            <x-primary-button>{{ __('Send request') }}</x-primary-button>
        </form>
    @endif
</div>
