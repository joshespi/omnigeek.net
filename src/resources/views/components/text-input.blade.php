@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-editorial-border bg-editorial-canvas text-editorial-heading placeholder:text-editorial-muted focus:border-editorial-accent focus:ring-editorial-accent']) }}>
