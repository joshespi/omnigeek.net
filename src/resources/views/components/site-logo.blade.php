<a href="{{ route('home') }}" wire:navigate {{ $attributes->class('inline-flex max-w-full items-center rounded-sm font-display text-2xl font-bold tracking-tight text-editorial-heading') }}>
    @if ($logoUrl = once(static fn () => \App\Support\SiteMedia::logoUrl()))
        <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" class="h-9 w-auto max-w-36 object-contain">
    @else
        {{ config('app.name', 'OmniGeek') }}
    @endif
</a>
