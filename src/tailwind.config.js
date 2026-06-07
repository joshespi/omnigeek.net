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
                sans: ['Lato', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#fef2f2',
                    100: '#fde3e1',
                    200: '#fbccc8',
                    300: '#f7a8a1',
                    400: '#f1746a',
                    500: '#e5342b',
                    600: '#d11f17',
                    700: '#af1812',
                    800: '#911813',
                    900: '#781a16',
                },
            },
        },
    },

    plugins: [forms, typography],
};
