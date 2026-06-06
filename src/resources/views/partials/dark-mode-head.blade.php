<script>
    window.applyTheme = function () {
        const dark = localStorage.theme === 'dark'
            || (! ('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', dark);
    };
    applyTheme();
    // wire:navigate morphs a fresh server-rendered <html> (no theme class) over the live
    // page, dropping the class; re-apply after each SPA navigation.
    document.addEventListener('livewire:navigated', applyTheme);
    document.addEventListener('livewire:navigating', applyTheme);
</script>
