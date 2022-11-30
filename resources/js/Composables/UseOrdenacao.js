/**
 * Quantidade de colunas visíveis.
 *
 * @see https://vuejs.org/guide/introduction.html
 * @see https://jsdoc.app/index.html
 * @see https://vue-toastification.maronato.dev/
 */

import { ref } from 'vue';

export const useOrdenacao = (ordenacaoInicial) => {
    // Ordenações aplicadas para exibição dos registros.
    // Ex: {campo1: direcao1, campo2:direcao2, ...}
    const ordenacoes = ref(ordenacaoInicial ?? {});

    const mudarOrdenacao = (campo, direcao) => {
        if (direcao === 'asc' || direcao === 'desc') {
            ordenacoes.value[campo] = direcao;
        } else {
            delete ordenacoes.value[campo];
        }
    };

    return { ordenacoes, mudarOrdenacao };
};
