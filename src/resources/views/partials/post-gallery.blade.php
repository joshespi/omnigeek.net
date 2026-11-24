@props(['items', 'editContext' => false, 'post' => null, 'cover' => false, 'priority' => false])

@php
    $count = $items->count();
    $nsfw = (bool) ($post?->nsfw);
    // An image's caption is its alt text; one without a caption is described by its post.
    $altBase = $post?->preview(80) ?? 'Post media';
    $alt = fn (int $n) => $items[$n]->caption ?: ($count > 1 ? $altBase.' ('.($n + 1).' of '.$count.')' : $altBase);
    $single = $count === 1;
    $mediaClasses = [
        'w-full',
        'absolute inset-0' => $cover,
        'h-full object-cover' => $cover || ! $single,
        'max-h-[85vh]' => ! $cover && $single,
    ];
@endphp

<div
    x-data="{
        open: false,
        current: 0,
        revealed: {{ \Illuminate\Support\Js::from(! $nsfw) }},
        items: {{ $items->map(fn($i, $n) => ['url' => $i->url(), 'type' => $i->type, 'alt' => $alt($n), 'caption' => $i->caption])->toJson() }},
        init() {
            this.$watch('open', () => this.pauseVideos());
            this.$watch('current', () => this.pauseVideos());
        },
        pauseVideos() { this.$root.querySelectorAll('video').forEach(v => v.pause()); },
        show(index) { if (! this.revealed) return; this.current = index; this.open = true; },
        prev() { this.current = (this.current - 1 + this.items.length) % this.items.length; },
        next() { this.current = (this.current + 1) % this.items.length; },
    }"
    x-on:keydown.left.window="open && prev()"
    x-on:keydown.right.window="open && next()"
    x-on:keydown.escape.window="open = false"
    @class(['editorial-cover' => $cover, 'mt-2' => ! $cover])
>
    {{-- Grid --}}
    <div @class(['relative', 'h-full' => $cover])>
        @if ($nsfw)
            {{-- NSFW gate: blur the grid until the viewer clicks to reveal. --}}
            <button type="button" x-show="! revealed" x-on:click="revealed = true"
                class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-1 rounded-lg bg-black/70 text-white">
                <span class="text-sm font-semibold tracking-wide">NSFW</span>
                <span class="text-xs text-white/80">Click to reveal</span>
            </button>
        @endif
        {{-- Blur is an inline style, not a Tailwind class: a `blur-*` class used only
             inside this dynamic x-bind would be purged from the compiled CSS. --}}
        <div
            @if ($nsfw)
                x-bind:style="revealed ? '' : 'filter: blur(28px);'"
                x-bind:class="revealed ? '' : 'pointer-events-none select-none'"
            @endif
            @class([
                'grid gap-1 overflow-hidden',
                'rounded-lg' => ! $cover,
                'h-full' => $cover,
                'grid-cols-1' => $cover || $single,
                'grid-cols-2' => ! $cover && $count >= 2,
            ])>
        @foreach ($items->take($cover ? 1 : 4) as $index => $item)
            @php
                $remaining = $loop->last ? $count - $loop->count : 0;
                $eager = $priority && $loop->first;
            @endphp
            <button
                type="button"
                wire:ignore
                @class([
                    'relative overflow-hidden bg-editorial-elevated focus-visible:outline-offset-[-4px]',
                    'col-span-2' => ! $cover && $count === 3 && $index === 2,
                    'aspect-square' => ! $cover && ! $single,
                ])
                x-on:click="show({{ $index }})"
                aria-label="{{ $item->isVideo() ? 'Play video' : 'View image' }} {{ $index + 1 }} of {{ $count }}"
            >
                @if ($item->isVideo())
                    <video src="{{ $item->url() }}"
                        @class($mediaClasses)
                        muted preload="{{ $cover && ! $eager ? 'none' : 'metadata' }}"></video>
                    <x-play-icon />
                @else
                    <img src="{{ $item->url() }}" alt="{{ $alt($index) }}" loading="{{ $eager ? 'eager' : 'lazy' }}"
                        @if ($eager) fetchpriority="high" @endif
                        @class($mediaClasses + ['object-contain' => ! $cover && $single]) />
                @endif

                {{-- Count the items beyond the visible thumbnails. --}}
                @if ($remaining > 0)
                    <span @class([
                        'absolute bg-black/60 text-white font-semibold',
                        'bottom-3 end-3 rounded-md px-2 py-1 text-sm' => $cover,
                        'inset-0 flex items-center justify-center text-xl' => ! $cover,
                    ])>+{{ $remaining }}</span>
                @endif
            </button>
        @endforeach
    </div>
    </div>

    {{-- Captions: one image gets a line under it; several get a numbered list.
         Hidden with the images while an NSFW post is unrevealed. --}}
    @if (! $cover && $items->contains(fn ($item) => filled($item->caption)))
        <div x-show="revealed" class="mt-2 text-sm italic text-gray-600 dark:text-gray-400">
            @if ($count === 1)
                <p>{{ $items->first()->caption }}</p>
            @else
                <ol class="space-y-0.5 list-none">
                    @foreach ($items as $item)
                        @if (filled($item->caption))
                            <li><span class="not-italic text-gray-400">{{ $loop->iteration }}.</span> {{ $item->caption }}</li>
                        @endif
                    @endforeach
                </ol>
            @endif
        </div>
    @endif

    {{-- Caption and delete controls (edit context) --}}
    @if ($editContext && $post?->canEdit(auth()->user()))
        <div class="mt-2 space-y-1">
            @foreach ($items as $item)
                <div wire:key="media-controls-{{ $item->id }}" class="flex items-center gap-2">
                    <span class="w-5 text-xs text-gray-400 text-right">#{{ $loop->iteration }}</span>
                    <input type="text" wire:model="captions.{{ $item->id }}" maxlength="500"
                        wire:keydown.enter.prevent="saveCaption({{ $item->id }})"
                        placeholder="Caption (optional)" aria-label="Caption for item {{ $loop->iteration }}"
                        class="flex-1 min-w-0 text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 py-1 px-2" />
                    <button type="button" wire:click="saveCaption({{ $item->id }})"
                        class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Save</button>
                    <button type="button"
                        wire:click="removeItem({{ $item->id }})"
                        wire:confirm="Remove this media?"
                        class="text-xs text-red-500 hover:underline">
                        Remove
                    </button>
                </div>
                <x-input-error :messages="$errors->get('captions.'.$item->id)" class="ml-7" />
            @endforeach
        </div>
    @endif

    {{-- Lightbox --}}
    <div
        x-show="open"
        x-cloak
        x-trap.noscroll="open"
        role="dialog"
        aria-modal="true"
        aria-label="Media viewer"
        class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
        x-on:click.self="open = false"
    >
        {{-- Close --}}
        <button type="button" x-on:click="open = false" aria-label="Close"
            class="absolute top-4 right-4 text-white/70 hover:text-white text-3xl leading-none">&times;</button>

        {{-- Prev --}}
        <button type="button" x-show="items.length > 1" x-on:click="prev()" aria-label="Previous"
            class="absolute left-4 text-white/70 hover:text-white text-3xl leading-none px-2">&#8249;</button>

        {{-- Media --}}
        <div class="max-w-4xl w-full max-h-screen px-16 flex items-center justify-center">
            <template x-if="open">
                <div class="w-full">
                    <template x-if="items[current].type === 'video'">
                        <video :src="items[current].url" controls class="max-h-[85vh] w-full rounded-lg"></video>
                    </template>
                    <template x-if="items[current].type !== 'video'">
                        <img :src="items[current].url" :alt="items[current].alt" class="max-h-[85vh] w-full object-contain rounded-lg" />
                    </template>
                    <p x-show="items[current].caption" x-text="items[current].caption" class="mt-2 text-center text-sm italic text-white/80"></p>
                </div>
            </template>
        </div>

        {{-- Next --}}
        <button type="button" x-show="items.length > 1" x-on:click="next()" aria-label="Next"
            class="absolute right-4 text-white/70 hover:text-white text-3xl leading-none px-2">&#8250;</button>

        {{-- Dots --}}
        <div x-show="items.length > 1" class="absolute bottom-4 flex gap-1.5">
            <template x-for="(item, i) in items" :key="i">
                <button type="button" x-on:click="current = i" :aria-label="'Go to item ' + (i + 1)"
                    :class="current === i ? 'bg-white' : 'bg-white/40'"
                    class="w-2 h-2 rounded-full transition-colors"></button>
            </template>
        </div>
    </div>
</div>
