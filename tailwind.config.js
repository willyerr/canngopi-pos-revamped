import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms'; 

/** @type {import('tailwindcss').Config} */
export default {
    presets: [ 
        require('./vendor/tallstackui/tallstackui/tailwind.config.js') 
    ],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './vendor/tallstackui/tallstackui/src/**/*.php', 
        './app/Providers/*.php', 
    ],
    theme: {
        extend: {
            colors: {
                'primary': {
                    DEFAULT: '#D12626',
                    50: "#FFEDE6",
                    100: "#FFD6CC",
                    200: "#FFB5AD",
                    300: "#FF908C",
                    400: "#FF6B6A",
                    500: "#D12626",
                    600: "#E64034",
                    700: "#CC3829",
                    800: "#B3301D",
                    900: "#991810",
                    950: "#800F0B"
                }
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        forms
    ],
};
