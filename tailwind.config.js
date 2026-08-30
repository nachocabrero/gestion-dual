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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                hlanz: {
                    blue: '#0048FE',
                    hover: '#003CD5',
                    light: '#EFF4FF',
                    dark: '#0F172A',
                    slate: '#1E293B',
                    cyan: '#0693E3',
                    accent: '#10B981',
                }
            },
            boxShadow: {
                'glass': '0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.04)',
                'glass-hover': '0 20px 30px -10px rgba(0, 72, 254, 0.12), 0 10px 15px -5px rgba(15, 23, 42, 0.06)',
                'glow': '0 0 20px rgba(0, 72, 254, 0.25)',
            }
        },
    },

    plugins: [forms],
};
