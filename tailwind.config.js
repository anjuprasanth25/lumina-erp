import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                lumina: {
                    darkBg: '#0b0f19',    // Deep background canvas
                    panelBg: '#111827',   // Header, sidebar, and cards
                    border: '#1f2937',    // Section dividers
                }
            }
        },
    },

    plugins: [forms],
};
