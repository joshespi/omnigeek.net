<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Categories</h1>
        <a href="{{ route('admin.home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:underline">← Admin</a>
    </div>

    <form wire:submit="create" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6 flex items-end gap-3">
        <div class="flex-1">
            <x-input-label for="name" :value="__('New category')" />
            <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" placeholder="e.g. Music" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>
        <x-primary-button>{{ __('Add') }}</x-primary-button>
    </form>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($categories as $category)
            <div wire:key="cat-{{ $category->id }}" class="flex items-center justify-between p-4">
                @if ($editingId === $category->id)
                    <form wire:submit="update" class="flex items-center gap-2 flex-1">
                        <x-text-input wire:model="editingName" type="text" class="block w-full" />
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <button type="button" wire:click="$set('editingId', null)" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                    </form>
                    <x-input-error :messages="$errors->get('editingName')" class="ml-2" />
                @else
                    <div>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</span>
                        <span class="text-sm text-gray-400">· {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <button wire:click="edit({{ $category->id }})" class="text-brand-600 dark:text-brand-400 hover:underline">Edit</button>
                        <button wire:click="delete({{ $category->id }})" wire:confirm="Delete this category? Posts keep their other categories." class="text-gray-400 hover:text-red-600">Delete</button>
                    </div>
                @endif
            </div>
        @empty
            <p class="p-4 text-center text-gray-500">No categories yet.</p>
        @endforelse
    </div>
</div>
