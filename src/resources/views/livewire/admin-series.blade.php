<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Series</h1>
        <a href="{{ route('admin.home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:underline">← Admin</a>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        A series is created by typing its name on a post. Here you can rename it and give it an introduction,
        which is shown at the top of its page.
    </p>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($allSeries as $series)
            <div wire:key="series-{{ $series->id }}" class="p-4">
                @if ($editingId === $series->id)
                    <form wire:submit="update" class="space-y-3">
                        <div>
                            <x-input-label for="series-name-{{ $series->id }}" :value="__('Name')" />
                            <x-text-input wire:model="editingName" id="series-name-{{ $series->id }}" type="text" class="mt-1 block w-full" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Renaming changes the series address, so old links to it stop working.</p>
                            <x-input-error :messages="$errors->get('editingName')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="series-description-{{ $series->id }}" :value="__('Introduction (Markdown, optional)')" />
                            <textarea wire:model="editingDescription" id="series-description-{{ $series->id }}" rows="6"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm"></textarea>
                            <x-input-error :messages="$errors->get('editingDescription')" class="mt-1" />
                        </div>
                        <div class="flex items-center gap-3">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <button type="button" wire:click="$set('editingId', null)" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                        </div>
                    </form>
                @else
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('series.show', $series) }}" wire:navigate class="font-medium text-gray-900 dark:text-gray-100 hover:underline">{{ $series->name }}</a>
                            <span class="text-sm text-gray-400">· {{ $series->posts_count }} {{ Str::plural('post', $series->posts_count) }}</span>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 truncate">
                                {{ $series->description ? Str::limit($series->description, 120) : 'No introduction yet.' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-sm shrink-0">
                            <button wire:click="edit({{ $series->id }})" class="text-brand-600 dark:text-brand-400 hover:underline">Edit</button>
                            <button wire:click="delete({{ $series->id }})" wire:confirm="Delete this series? Its posts stay, but leave the series." class="text-gray-400 hover:text-red-600">Delete</button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <p class="p-4 text-center text-gray-500">No series yet. Type a series name on a post to start one.</p>
        @endforelse
    </div>
</div>
