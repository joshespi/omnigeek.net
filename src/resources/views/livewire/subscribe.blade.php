<div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100 mb-2">Subscribe</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Get an email when new posts go up. Pick filters to narrow it down, or leave them empty for everything.</p>

    @if ($done)
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 text-center">
            <p class="text-gray-800 dark:text-gray-200">Almost there. Check your inbox for a confirmation link.</p>
        </div>
    @else
        <form wire:submit="save" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-5">
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" placeholder="you@example.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            @if ($allCategories->isNotEmpty())
                <div>
                    <x-input-label :value="__('Categories (optional)')" />
                    <div class="mt-2">
                        <x-checkbox-pills :items="$allCategories" model="categories" value="slug" />
                    </div>
                </div>
            @endif

            @if ($allGeeks->isNotEmpty())
                <div>
                    <x-input-label :value="__('Geeks (optional)')" />
                    <div class="mt-2">
                        <x-checkbox-pills :items="$allGeeks" model="geeks" />
                    </div>
                </div>
            @endif

            <div>
                <x-input-label for="sub-tags" :value="__('Tags (optional)')" />
                <x-text-input wire:model="tags" id="sub-tags" type="text" class="mt-1 block w-full" placeholder="rust homelab" />
            </div>

            <x-primary-button>{{ __('Subscribe') }}</x-primary-button>
        </form>
    @endif
</div>
