<div>
    @if ($editing)
        <form wire:submit="save" class="mt-3 border-t border-gray-200 dark:border-gray-700 pt-3">
            <input type="text" wire:model="title" placeholder="Title (optional)"
                class="w-full mb-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm font-medium" />
            <x-input-error :messages="$errors->get('title')" class="mt-1" />

            <div x-data="mdToolbar()" class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 px-2 py-1">
                    <div class="flex gap-1">
                        <button type="button" @click="wrap('**', '**', 'bold text')" title="Bold"
                            class="px-2 py-0.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">B</button>
                        <button type="button" @click="wrap('*', '*', 'italic text')" title="Italic"
                            class="px-2 py-0.5 text-xs italic text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">I</button>
                        <button type="button" @click="insertLink()" title="Link"
                            class="px-2 py-0.5 text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">Link</button>
                        <button type="button" @click="insertPrefix('- ')" title="List"
                            class="px-2 py-0.5 text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">• List</button>
                        <button type="button" @click="insertPrefix('> ')" title="Quote"
                            class="px-2 py-0.5 text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">Quote</button>
                        <button type="button" @click="wrap('`', '`', 'code')" title="Code"
                            class="px-2 py-0.5 text-xs font-mono text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">Code</button>
                    </div>
                    <button type="button" wire:click="togglePreview"
                        class="text-xs text-brand-600 dark:text-brand-400 hover:underline px-2">
                        {{ $showPreview ? 'Write' : 'Preview' }}
                    </button>
                </div>

                @if ($showPreview)
                    <div class="min-h-[80px] p-3">
                        @if (trim($body))
                            <div class="prose prose-sm dark:prose-invert max-w-none break-words">
                                {!! $this->renderedBody() !!}
                            </div>
                        @else
                            <p class="text-gray-400 text-sm">Nothing to preview.</p>
                        @endif
                    </div>
                @else
                    <textarea x-ref="textarea" wire:model="body" rows="4"
                        class="w-full border-0 dark:bg-gray-700 dark:text-gray-100 resize-none focus:ring-0 p-3 text-sm"></textarea>
                @endif
            </div>
            <x-input-error :messages="$errors->get('body')" class="mt-1" />

            <input type="text" wire:model="youtube" placeholder="YouTube link (optional)"
                class="w-full mt-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            <x-input-error :messages="$errors->get('youtube')" class="mt-1" />

            @if ($categories->isNotEmpty())
                <div class="mt-3">
                    <x-checkbox-pills :items="$categories" model="selectedCategories" />
                </div>
                <x-input-error :messages="$errors->get('selectedCategories')" class="mt-1" />
            @endif

            <input type="text" wire:model="tags" list="tag-hints-editor" placeholder="Tags, space-separated"
                class="w-full mt-3 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            @if ($tagHints->isNotEmpty())
                <datalist id="tag-hints-editor">
                    @foreach ($tagHints as $hint)
                        <option value="{{ $hint }}"></option>
                    @endforeach
                </datalist>
            @endif
            <x-input-error :messages="$errors->get('tags')" class="mt-1" />

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
