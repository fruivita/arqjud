/**
 * @link https://tailwindcss.com/docs/content-configuration
 * @link https://tailwindcss.com/docs/dark-mode
 * @link https://tailwindcss.com/docs/presets
 */

module.exports = {
    /**
     * Deve-se adicionar o path de qualquer classe css que seja adicionada fora
     * da locação padrão que é a pasta './resources'.
     * Caso contrário, a clase não será gerada pelo compilador e o estilo
     * definido consequentemente não será aplicado.
     */
    content: ['./resources/**/*.blade.php', './resources/**/*.js', './resources/**/*.vue'],

    darkMode: 'class',

    theme: {
        extend: {},
    },

    plugins: [],
};
