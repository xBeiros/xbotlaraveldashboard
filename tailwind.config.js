import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'dark-800': 'rgb(31 33 41)',
                'dark-900': '#202225',
                'dark-300': 'rgb(148 155 164)',
                'brand-dark': '#36393f',
                'brand-hover': '#5865f2',
                'brand-default': '#5865f2',
                'success-default': '#3ba55d',
            },
        },
    },

    plugins: [forms],
};
