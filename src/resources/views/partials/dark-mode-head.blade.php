<script data-navigate-once>
    (() => {
        let preference = 'dark';

        function applyTheme() {
            document.documentElement.classList.toggle('dark', preference === 'dark');
        }

        try {
            preference = localStorage.getItem('theme') === 'light' ? 'light' : 'dark';
        } catch {
            // Fall back to the default if storage is unavailable.
        }

        window.setOmniGeekTheme = (theme) => {
            preference = theme;

            try {
                localStorage.setItem('theme', theme);
            } catch {
                // The toggle still works for this document.
            }

            applyTheme();
        };

        applyTheme();
        document.addEventListener('livewire:navigated', applyTheme);
    })();
</script>
