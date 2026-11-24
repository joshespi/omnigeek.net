@props(['active' => null, 'sectionActive' => false])

@php($active ??= request()->url() === $attributes->get('href'))

<a @if ($active) aria-current="page" @endif
    {{ $attributes->class(['site-menu-link [overflow-wrap:anywhere]', 'font-semibold underline decoration-2 underline-offset-8' => $active || $sectionActive]) }}>
    {{ $slot }}
</a>
