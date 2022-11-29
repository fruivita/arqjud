/**
 * @see https://tailwindcss.com/docs/customizing-colors
 * @see https://tailwindcss.com/docs/presets
 */

const colors = require('tailwindcss/colors');

module.exports = {
    presets: [require('./tailwind-preset')],

    // Essa configuração sofrerá merge
    theme: {
        extend: {
            colors: {
                primaria: colors.teal,
                secundaria: colors.slate,
            },
        },
    },
};
