<div>
    @if ($editing)
        <form wire:submit="save" class="mt-3 border-t border-gray-200 dark:border-gray-700 pt-3">
            <input type="text" wire:model="form.title" placeholder="Title (optional)"
                class="w-full mb-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm font-medium" />
            <x-input-error :messages="$errors->get('form.title')" class="mt-1" />

            <x-markdown-editor model="form.body" :body="$form->body" :show-preview="$showPreview" :rows="8" />
            <x-input-error :messages="$errors->get('form.body')" class="mt-1" />

            <input type="text" wire:model="form.youtube" placeholder="YouTube link (optional)"
                class="w-full mt-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            <x-input-error :messages="$errors->get('form.youtube')" class="mt-1" />

            @if ($categories->isNotEmpty())
                <div class="mt-3">
                    <x-checkbox-pills :items="$categories" model="form.selectedCategories" />
                </div>
                <x-input-error :messages="$errors->get('form.selectedCategories')" class="mt-1" />
            @endif

            <input type="text" wire:model="form.tags" list="tag-hints-editor" placeholder="Tags, space-separated"
                class="w-full mt-3 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            @if ($tagHints->isNotEmpty())
                <datalist id="tag-hints-editor">
                    @foreach ($tagHints as $hint)
                        <option value="{{ $hint }}"></option>
                    @endforeach
                </datalist>
            @endif
            <x-input-error :messages="$errors->get('form.tags')" class="mt-1" />

            <div class="flex items-center gap-3 mt-3">
                <x-primary-button>Save</x-primary-button>
                <button type="button" wire:click="cancelEditing"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Cancel</button>
            </div>
        </form>
    @elseif ($post->canEdit(auth()->user()))
        <button type="button" wire:click="startEditing"
            class="mt-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">Edit</button>
    @endif
</div>
