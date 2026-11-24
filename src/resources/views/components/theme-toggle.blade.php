<button type="button"
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    x-on:livewire:navigated.document="dark = document.documentElement.classList.contains('dark')"
    x-on:click="dark = ! document.documentElement.classList.contains('dark'); window.setOmniGeekTheme(dark ? 'dark' : 'light')"
    :aria-pressed="dark"
    aria-label="Dark mode"
    title="Toggle dark mode"
    {{ $attributes->class('site-control') }}>
    <svg aria-hidden="true" class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
    </svg>
    <svg aria-hidden="true" class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.72 0l-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
</button>
