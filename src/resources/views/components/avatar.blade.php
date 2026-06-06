@props(['user', 'size' => 'md', 'url' => null])

@php
    [$box, $text] = match ($size) {
        'sm' => ['h-6 w-6', 'text-xs'],
        'lg' => ['h-16 w-16', 'text-xl'],
        default => ['h-9 w-9', 'text-sm'],
    };
    $src = $url ?? $user?->avatar_url;
@endphp

@if ($src)
    <img src="{{ $src }}" {{ $attributes->merge(['class' => "$box rounded-full object-cover"]) }} alt="" />
@else
    <span {{ $attributes->merge(['class' => "$box $text rounded-full bg-brand-600 text-white flex items-center justify-center font-semibold"]) }}>{{ $user->initials() }}</span>
@endif
