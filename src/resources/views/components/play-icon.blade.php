@props(['size' => 'md'])

@php
    [$badge, $icon] = $size === 'lg' ? ['h-14 w-14', 'h-8 w-8'] : ['h-11 w-11', 'h-6 w-6'];
@endphp

<span aria-hidden="true" class="absolute inset-0 flex items-center justify-center">
    <span class="{{ $badge }} flex items-center justify-center rounded-full border border-white/80 bg-black/70">
        <svg class="{{ $icon }} ml-0.5 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
        </svg>
    </span>
</span>
