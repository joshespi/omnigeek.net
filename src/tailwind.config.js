import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Space Grotesk', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                editorial: {
                    canvas: 'var(--editorial-canvas)',
                    surface: 'var(--editorial-surface)',
                    elevated: 'var(--editorial-elevated)',
                    accent: 'var(--editorial-accent)',
                    heading: 'var(--editorial-heading)',
                    body: 'var(--editorial-body)',
                    muted: 'var(--editorial-muted)',
                    border: 'var(--editorial-border)',
                },
                brand: {
                    50: '#f5f2ff',
                    100: '#ebe5ff',
                    200: '#ddd4ff',
                    300: '#c6baff',
                    400: '#a99aff',
                    500: '#7155e8',
                    600: '#6247d5',
                    700: '#5138b7',
                    800: '#412d94',
                    900: '#342676',
                },
            },
        },
    },

    plugins: [forms, typography],
};
