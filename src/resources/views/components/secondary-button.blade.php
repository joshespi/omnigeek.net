<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-lg border border-editorial-border bg-editorial-surface px-4 py-2 text-sm font-semibold text-editorial-body transition-colors hover:bg-editorial-elevated hover:text-editorial-heading disabled:opacity-60']) }}>
    {{ $slot }}
</button>
