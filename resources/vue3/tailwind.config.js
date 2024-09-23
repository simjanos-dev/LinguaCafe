const primeui = require('tailwindcss-primeui');

module.exports = {
    darkMode: 'selector',

    content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
    plugins: [primeui],
    theme: {
        extend: {},
    },
};
