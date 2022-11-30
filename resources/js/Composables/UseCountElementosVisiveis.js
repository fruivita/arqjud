/**
 * Quantidade de elementos ocultáveis visíveis na página.
 *
 * @see https://vuejs.org/guide/introduction.html
 * @see https://jsdoc.app/index.html
 * @see https://vue-toastification.maronato.dev/
 */

import { pickBy } from 'lodash';

/**
 * Quantidade de elementos ocultáveis visíveis.
 *
 * @param {Object} elementos - {elemento1: true, elemento2: true, elemento3: false}
 * @returns {Number} quantidade de elementos avalidas como true.
 */
export const countElementosVisiveis = (elementos) => {
    return Object.values(pickBy(elementos.value)).length;
};
