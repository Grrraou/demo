import defaultTheme from 'tailwindcss/defaultTheme';

export default {
    content: [
        './vendor/livewire/livewire/src/Component.php',
        './vendor/livewire/livewire/src/Livewire.php',
        './resources/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
