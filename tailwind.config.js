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
        },
    },

    plugins: [forms],
};

module.exports = {
  darkMode: 'class', // Enable class-based dark mode toggling
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

// module.exports = {
//   content: ["./resources/**/*.blade.php", "./resources/**/*.js"],
//   theme: {
//     extend: {
//       animation: {
//         blink: 'blink 1s step-start infinite',
//       },
//       keyframes: {
//         blink: {
//           '0%, 100%': { opacity: '1' },
//           '50%': { opacity: '0' },
//         },
//       },
//     },
//   },
//   plugins: [],
// }