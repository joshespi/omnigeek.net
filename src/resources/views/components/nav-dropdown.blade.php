@props(['label' => null, 'routePrefix' => null, 'align' => 'left'])

<details x-data
    x-on:click.outside="$el.open = false"
    x-on:keydown.escape="if ($el.open) { $event.stopPropagation(); $el.open = false; $refs.summary.focus(); }"
    class="group relative">
    <summary x-ref="summary" @class([
        'site-menu-link cursor-pointer select-none [&::-webkit-details-marker]:hidden',
        'font-semibold underline decoration-2 underline-offset-8' => $routePrefix && request()->routeIs($routePrefix.'.*'),
    ])>
        {{ $trigger ?? $label }}
        <svg aria-hidden="true" class="h-4 w-4 shrink-0 transition-transform group-open:rotate-180" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="m4 6 4 4 4-4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </summary>
    <div @class([
        'ms-3 border-s border-editorial-border ps-2 lg:absolute lg:top-full lg:ms-0 lg:max-h-[min(26rem,70vh)] lg:w-52 lg:overflow-y-auto lg:rounded-lg lg:border lg:bg-editorial-surface lg:p-1',
        'lg:end-0' => $align === 'right',
        'lg:start-0' => $align !== 'right',
    ])>
        {{ $slot }}
    </div>
</details>
