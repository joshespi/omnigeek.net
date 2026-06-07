<div class="max-w-3xl mx-auto px-4 py-8">
    @auth
        <form wire:submit="save" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6">
            <input type="text" wire:model="title" placeholder="Title (optional)"
                class="w-full mb-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm font-medium" />
            <x-input-error :messages="$errors->get('title')" class="mt-1" />

            <div x-data="mdToolbar()" class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                {{-- Tab bar --}}
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
                    <textarea x-ref="textarea" wire:model="body" rows="3"
                        placeholder="What's on your mind? Markdown supported."
                        class="w-full border-0 dark:bg-gray-700 dark:text-gray-100 resize-none focus:ring-0 p-3"></textarea>
                @endif
            </div>
            <x-input-error :messages="$errors->get('body')" class="mt-1" />

            <input type="text" wire:model="youtube" placeholder="Paste a YouTube link (optional)"
                class="w-full mt-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            <x-input-error :messages="$errors->get('youtube')" class="mt-1" />

            @if ($categories->isNotEmpty())
                <div class="mt-3">
                    <x-checkbox-pills :items="$categories" model="selectedCategories" />
                </div>
                <x-input-error :messages="$errors->get('selectedCategories')" class="mt-1" />
            @endif

            <input type="text" wire:model="tags" list="tag-hints" placeholder="Add tags, space-separated (e.g. rust homelab)"
                class="w-full mt-3 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
            @if ($tagHints->isNotEmpty())
                <datalist id="tag-hints">
                    @foreach ($tagHints as $hint)
                        <option value="{{ $hint }}"></option>
                    @endforeach
                </datalist>
                <p class="mt-1 text-xs text-gray-400">Existing tags: {{ $tagHints->take(12)->implode(', ') }}{{ $tagHints->count() > 12 ? '…' : '' }}</p>
            @endif
            <x-input-error :messages="$errors->get('tags')" class="mt-1" />

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

    <x-posts-feed :posts="$posts" />
</div>

<script>
function mdToolbar() {
    return {
        notify(ta) {
            ta.dispatchEvent(new Event('input', { bubbles: true }));
            ta.focus();
        },
        wrap(before, after, placeholder) {
            const ta = this.$refs.textarea;
            if (!ta) return;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const selected = ta.value.slice(start, end) || placeholder;
            ta.setRangeText(before + selected + after, start, end, 'select');
            this.notify(ta);
        },
        insertLink() {
            const ta = this.$refs.textarea;
            if (!ta) return;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const selected = ta.value.slice(start, end) || 'link text';
            ta.setRangeText('[' + selected + '](url)', start, end, 'end');
            this.notify(ta);
        },
        insertPrefix(prefix) {
            const ta = this.$refs.textarea;
            if (!ta) return;
            const start = ta.selectionStart;
            const lineStart = ta.value.lastIndexOf('\n', start - 1) + 1;
            ta.setRangeText(prefix, lineStart, lineStart, 'end');
            this.notify(ta);
        },
    };
}
</script>
