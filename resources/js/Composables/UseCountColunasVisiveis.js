/**
 * Quantidade de colunas visíveis.
 *
 * @see https://vuejs.org/guide/introduction.html
 * @see https://github.com/VitorLuizC/vue-uuid
 * @see https://jsdoc.app/index.html
 * @see https://vue-toastification.maronato.dev/
 */

import { pickBy } from 'lodash';

/**
 * Quantidade de colunas visíveis.
 *
 * @param {Object} colunas - {coluna1: true, coluna2: true, coluna3: false}
 * @returns {Number} quantidade de colunas avalidas como true.
 */
export const countColunasVisiveis = (colunas) => {
    return Object.values(pickBy(colunas.value)).length;
};
