@props(['size' => 'md'])

@php
    [$badge, $icon] = $size === 'lg' ? ['w-14 h-14 bg-black/60', 'w-7 h-7 ml-1'] : ['w-10 h-10 bg-black/50', 'w-5 h-5 ml-0.5'];
@endphp

<span class="absolute inset-0 flex items-center justify-center">
    <span class="{{ $badge }} rounded-full flex items-center justify-center">
        <svg class="{{ $icon }} text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
        </svg>
    </span>
</span>
