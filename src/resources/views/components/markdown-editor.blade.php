@props(['model', 'body' => '', 'showPreview' => false, 'rows' => 12, 'uploads' => true])

@php
    // A small, geek-leaning starter set for the emoji picker. Native unicode — stored
    // and rendered as-is (the DB is utf8mb4); the picker just makes them discoverable.
    $emojis = ['😀','😂','😅','😍','😎','🤔','🤯','🥳','😴','🙃','👍','👏','🙏','🔥','💯','⚡','🎉','🚀','💡','✅','❌','👀','❤️','💀','🤖','👾','🐛','📦','🕹️','🎮','💻','🛠️','📈','☕'];

    // Shared styling for every toolbar button; per-button modifiers (font-bold, etc.) appended.
    $btn = 'px-2 py-0.5 text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded';
@endphp

<div
    x-data="{
        ...mdToolbar(),
        emojiOpen: false,
        emojis: @js($emojis),
        sel: { start: 0, end: 0 },
        awaitingUpload: false,
        saveSel() {
            const ta = this.$refs.textarea;
            if (ta) this.sel = { start: ta.selectionStart, end: ta.selectionEnd };
        },
        insertAtSaved(text, mode = 'end') {
            const ta = this.$refs.textarea;
            if (!ta) return;
            ta.focus();
            ta.setRangeText(text, this.sel.start, this.sel.end, mode);
            this.sel = { start: ta.selectionStart, end: ta.selectionEnd };
            this.notify(ta);
        },
        pickEmoji(e) {
            this.insertAtSaved(e);
            this.emojiOpen = false;
        },
        pickImage() {
            this.saveSel();
            this.awaitingUpload = true;
            this.$refs.imageInput && this.$refs.imageInput.click();
        },
        onImageUploaded(url) {
            // Only the editor that started the upload reacts — the event is global but
            // several editors may be mounted (compose modal + inline post editors).
            if (!this.awaitingUpload) return;
            this.awaitingUpload = false;
            this.insertAtSaved('![](' + url + ')');
        },
    }"
    @if ($uploads) x-on:inline-image-uploaded.window="onImageUploaded($event.detail.url)" @endif
    class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden"
>
    {{-- Toolbar wraps to multiple rows on narrow screens instead of overflowing. --}}
    <div class="flex flex-wrap items-center gap-1 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 px-2 py-1">
            <button type="button" @click="wrap('**', '**', 'bold text')" title="Bold"
                class="{{ $btn }} font-bold">B</button>
            <button type="button" @click="wrap('*', '*', 'italic text')" title="Italic"
                class="{{ $btn }} italic">I</button>
            <button type="button" @click="insertLink()" title="Link"
                class="{{ $btn }}">Link</button>
            <button type="button" @click="insertPrefix('- ')" title="List"
                class="{{ $btn }}">• List</button>
            <button type="button" @click="insertPrefix('> ')" title="Quote"
                class="{{ $btn }}">Quote</button>
            <button type="button" @click="wrap('`', '`', 'code')" title="Code"
                class="{{ $btn }} font-mono">Code</button>

            @if ($uploads)
                {{-- Inline image: uploads through the same pipeline as gallery media, then
                     drops a ![](url) at the cursor. --}}
                <button type="button" @click="pickImage()" x-bind:disabled="awaitingUpload" title="Insert image"
                    class="{{ $btn }} disabled:opacity-50">
                    <span x-show="!awaitingUpload">🖼 Image</span>
                    <span x-show="awaitingUpload" x-cloak>Uploading…</span>
                </button>
            @endif

            {{-- Emoji picker: inserts native unicode at the cursor. --}}
            <div class="relative">
                <button type="button" @click="saveSel(); emojiOpen = !emojiOpen" title="Emoji"
                    class="{{ $btn }}">😀</button>
                <div x-show="emojiOpen" x-cloak x-transition
                    @click.outside="emojiOpen = false"
                    class="absolute z-20 mt-1 left-0 w-56 max-h-44 overflow-y-auto grid grid-cols-8 gap-0.5 p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg">
                    <template x-for="e in emojis" :key="e">
                        <button type="button" @click="pickEmoji(e)" x-text="e"
                            class="text-lg leading-none p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700"></button>
                    </template>
                </div>
            </div>

        <button type="button" wire:click="togglePreview"
            class="text-xs text-brand-600 dark:text-brand-400 hover:underline px-2 ml-auto">
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
            @click="saveSel()" @keyup="saveSel()" @select="saveSel()"
            placeholder="What's on your mind? Markdown supported."
            class="w-full border-0 dark:bg-gray-700 dark:text-gray-100 resize-y focus:ring-0 p-3 text-sm"></textarea>
    @endif

    @if ($uploads)
        <input type="file" x-ref="imageInput" wire:model="inlineImage" accept="image/*" class="hidden" />
        <p x-show="awaitingUpload" x-cloak class="px-3 pb-2 text-xs text-gray-400">Uploading image…</p>
        @error('inlineImage')
            <p class="px-3 pb-2 text-xs text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>
