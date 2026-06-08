@props(['items', 'editContext' => false, 'post' => null])

@php
    $count = $items->count();
    $nsfw = (bool) ($post?->nsfw);
@endphp

<div
    x-data="{
        open: false,
        current: 0,
        revealed: {{ \Illuminate\Support\Js::from(! $nsfw) }},
        items: {{ $items->map(fn($i) => ['url' => $i->url(), 'type' => $i->type])->toJson() }},
        show(index) { if (! this.revealed) return; this.current = index; this.open = true; },
        prev() { this.current = (this.current - 1 + this.items.length) % this.items.length; },
        next() { this.current = (this.current + 1) % this.items.length; },
    }"
    x-on:keydown.left.window="open && prev()"
    x-on:keydown.right.window="open && next()"
    x-on:keydown.escape.window="open = false"
    class="mt-2"
>
    {{-- Grid --}}
    <div class="relative">
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
                'grid gap-1 rounded-lg overflow-hidden',
                'grid-cols-1' => $count === 1,
                'grid-cols-2' => $count >= 2,
            ])>
        @php
            // A lone image shows uncropped at its natural ratio (capped height); tiles in a
            // multi-image grid stay square-cropped so the grid reads cleanly.
            $single = $count === 1;
        @endphp
        @foreach ($items->take(4) as $index => $item)
            @php $isLast = $index === 3 && $count > 4; @endphp
            <button
                type="button"
                wire:ignore
                @class([
                    'relative overflow-hidden bg-gray-200 dark:bg-gray-700',
                    'col-span-2' => $count === 3 && $index === 2,
                    'aspect-square' => ! $single,
                ])
                x-on:click="show({{ $index }})"
            >
                @if ($item->isVideo())
                    <video src="{{ $item->url() }}"
                        @class(['w-full', 'h-full object-cover' => ! $single, 'max-h-[85vh]' => $single])
                        muted preload="metadata"></video>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-black/50 rounded-full w-10 h-10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                            </svg>
                        </div>
                    </div>
                @else
                    <img src="{{ $item->url() }}" alt="" loading="lazy"
                        @class(['w-full', 'h-full object-cover' => ! $single, 'max-h-[85vh] object-contain' => $single]) />
                @endif

                {{-- "+N more" overlay on 4th tile --}}
                @if ($isLast)
                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                        <span class="text-white text-xl font-semibold">+{{ $count - 4 }}</span>
                    </div>
                @endif
            </button>
        @endforeach
    </div>
    </div>

    {{-- Delete buttons (edit context) --}}
    @if ($editContext && $post?->canEdit(auth()->user()))
        <div class="flex flex-wrap gap-1 mt-1">
            @foreach ($items as $item)
                <button type="button"
                    wire:click="removeItem({{ $item->id }})"
                    wire:confirm="Remove this media?"
                    class="text-xs text-red-500 hover:underline">
                    Remove #{{ $loop->iteration }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Lightbox --}}
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
        x-on:click.self="open = false"
    >
        {{-- Close --}}
        <button type="button" x-on:click="open = false"
            class="absolute top-4 right-4 text-white/70 hover:text-white text-3xl leading-none">&times;</button>

        {{-- Prev --}}
        <button type="button" x-show="items.length > 1" x-on:click="prev()"
            class="absolute left-4 text-white/70 hover:text-white text-3xl leading-none px-2">&#8249;</button>

        {{-- Media --}}
        <div class="max-w-4xl w-full max-h-screen px-16 flex items-center justify-center">
            <template x-for="(item, i) in items" :key="i">
                <div x-show="current === i" class="w-full">
                    <template x-if="item.type === 'video'">
                        <video :src="item.url" controls class="max-h-[85vh] w-full rounded-lg"></video>
                    </template>
                    <template x-if="item.type !== 'video'">
                        <img :src="item.url" alt="" class="max-h-[85vh] w-full object-contain rounded-lg" />
                    </template>
                </div>
            </template>
        </div>

        {{-- Next --}}
        <button type="button" x-show="items.length > 1" x-on:click="next()"
            class="absolute right-4 text-white/70 hover:text-white text-3xl leading-none px-2">&#8250;</button>

        {{-- Dots --}}
        <div x-show="items.length > 1" class="absolute bottom-4 flex gap-1.5">
            <template x-for="(item, i) in items" :key="i">
                <button type="button" x-on:click="current = i"
                    :class="current === i ? 'bg-white' : 'bg-white/40'"
                    class="w-2 h-2 rounded-full transition-colors"></button>
            </template>
        </div>
    </div>
</div>
