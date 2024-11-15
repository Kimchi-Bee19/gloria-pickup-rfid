import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import flowbite from 'flowbite/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'false',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/js/**/*.svelte',
        './resources/views/**/*.blade.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors:{
                'dark-blue': 'var(--dark-blue)',
                'light-blue': 'var(--light-blue)',
                'green-2': '#4fa55c',
                'green-1': '#a9ffb6',
                'red-1': '#df2121',
                'blue-3': '#003357',
                'blue-2': '#0b8bcc',
                'blue-1': '#c5eaff'
            },
            backgroundImage:{
                'gradient-to-top': 'linear-gradient(to top, var(--dark-blue), var(--light-blue))',
            },
        },
    },

    plugins: [forms],
};
