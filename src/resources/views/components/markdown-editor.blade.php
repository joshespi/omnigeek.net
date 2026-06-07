@props(['model', 'body' => '', 'showPreview' => false, 'rows' => 12])

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
        <div class="min-h-[120px] p-3">
            @if (trim($body))
                <x-markdown :text="$body" />
            @else
                <p class="text-gray-400 text-sm">Nothing to preview.</p>
            @endif
        </div>
    @else
        <textarea x-ref="textarea" wire:model="{{ $model }}" rows="{{ $rows }}"
            placeholder="What's on your mind? Markdown supported."
            class="w-full border-0 dark:bg-gray-700 dark:text-gray-100 resize-y focus:ring-0 p-3 text-sm"></textarea>
    @endif
</div>
