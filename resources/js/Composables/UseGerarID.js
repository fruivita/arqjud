/**
 * Gera ids no padrão UUID.
 *
 * @see https://vuejs.org/guide/introduction.html
 * @see https://github.com/VitorLuizC/vue-uuid
 * @see https://jsdoc.app/index.html
 */

import { uuid } from 'vue-uuid';

/**
 * Gera ids no padrão UUID.
 *
 * @returns {String} id gerado
 */
export const gerarID = () => {
    return uuid.v4();
};
