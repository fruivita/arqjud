/**
 * Disponibiliza as translations para uso no frontend.
 *
 * As translations são produzidas pelo backend laravel.
 *
 * @see https://vuejs.org/guide/introduction.html
 * @see https://github.com/VitorLuizC/vue-uuid
 * @see https://jsdoc.app/index.html
 * @see https://lodash.com/
 * @see https://www.youtube.com/watch?v=IZIzcjDdPIw
 */

import { forEach, keys, replace } from 'lodash';
import { defineStore } from 'pinia';

export const useTranslationsStore = defineStore('TranslationsStore', () => {
    /**
     * Retorna a string da chave informada.
     *
     * Se a chave não existir, a própria chave será retornada.
     *
     * @param {String=} chave
     * @param {Object=} substituicoes
     * @returns {String} string pronta para uso
     */
    const __ = (chave, substituicoes = {}) => {
        let translation = window._translations?.[chave] || chave;

        forEach(keys(substituicoes), (trecho) => {
            translation = replace(translation, `:${trecho}`, substituicoes[trecho]);
        });

        return translation;
    };

    return { __ };
});
