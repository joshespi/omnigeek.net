<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Site media</h1>
        <a href="{{ route('admin.home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:underline">← Admin</a>
    </div>

    {{-- Logo --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
        <h2 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Logo</h2>

        @if ($logoUrl)
            <div class="mb-4 flex items-center gap-4">
                <img src="{{ $logoUrl }}" alt="Site logo" class="h-12 object-contain bg-gray-100 dark:bg-gray-700 rounded p-1" />
                <button wire:click="deleteLogo" wire:confirm="Remove logo?"
                    class="text-sm text-red-500 hover:text-red-700">Remove</button>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No logo uploaded. The app name text is shown in the nav.</p>
        @endif

        <form wire:submit="saveLogo" class="space-y-3">
            <div>
                <x-input-label value="Upload new logo (PNG/JPG, max 2 MB)" />
                <input type="file" wire:model="logo" accept="image/*" class="mt-1 text-sm text-gray-600 dark:text-gray-300" />
                <x-input-error :messages="$errors->get('logo')" class="mt-1" />
                <div wire:loading wire:target="logo" class="text-xs text-gray-400 mt-1">Uploading…</div>
            </div>
            @if ($logo)
                <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="h-12 object-contain rounded" />
            @endif
            <x-primary-button>Save logo</x-primary-button>
            <x-action-message on="logo-saved">Saved.</x-action-message>
        </form>
    </div>

    {{-- Digest cadence --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
        <h2 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Digest cadence</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">How often weekly-digest subscribers get an email. Takes effect on the next scheduler run.</p>

        <form wire:submit="saveDigestCadence" class="space-y-3">
            <div class="flex gap-4 text-sm">
                @foreach (\App\Enums\DigestCadence::options() as $value => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model="digestCadence" value="{{ $value }}"
                            class="text-brand-600 focus:ring-brand-500" />
                        <span class="text-gray-700 dark:text-gray-300">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('digestCadence')" class="mt-1" />
            <x-primary-button>Save cadence</x-primary-button>
            <x-action-message on="cadence-saved">Saved.</x-action-message>
        </form>
    </div>

    {{-- OG default image --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <h2 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Default OG image</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Shown when a post or page has no image. Recommended: 1200×630 px.</p>

        @if ($ogImageUrl)
            <div class="mb-4">
                <img src="{{ $ogImageUrl }}" alt="OG default" class="max-h-40 rounded object-cover" />
                <button wire:click="deleteOgImage" wire:confirm="Remove OG image?"
                    class="mt-2 block text-sm text-red-500 hover:text-red-700">Remove</button>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No OG image uploaded. Posts without media will share without a preview image.</p>
        @endif

        <form wire:submit="saveOgImage" class="space-y-3">
            <div>
                <x-input-label value="Upload OG image (PNG/JPG, min 600×315, max 5 MB)" />
                <input type="file" wire:model="ogImage" accept="image/*" class="mt-1 text-sm text-gray-600 dark:text-gray-300" />
                <x-input-error :messages="$errors->get('ogImage')" class="mt-1" />
                <div wire:loading wire:target="ogImage" class="text-xs text-gray-400 mt-1">Uploading…</div>
            </div>
            @if ($ogImage)
                <img src="{{ $ogImage->temporaryUrl() }}" alt="Preview" class="max-h-32 rounded object-cover" />
            @endif
            <x-primary-button>Save OG image</x-primary-button>
            <x-action-message on="og-saved">Saved.</x-action-message>
        </form>
    </div>
</div>
