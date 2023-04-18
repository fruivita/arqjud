/**
 * Store para disponibilização global dos dados estáticos da aplicação.
 *
 * @see https://vuejs.org/
 * @see https://pinia.vuejs.org/
 */

import { defineStore } from 'pinia';

export const useDadosEstaticosStore = defineStore('DadosEstaticosStore', () => {
    /**
     * Nome da aplicação.
     *
     *  @type {String}
     */
    const ambiente = window._dados.ambiente;

    /**
     * Nome da aplicação.
     *
     *  @type {String}
     */
    const appNome = window._dados.app_nome;

    /**
     * Nome completo da aplicação.
     *
     *  @type {String}
     */
    const appNomeCompleto = window._dados.app_nome_completo;

    /**
     * Versão da aplicação.
     *
     *  @type {String}
     */
    const appVersao = window._dados.app_versao;

    /**
     * Sigla do órgão.
     *
     *  @type {String}
     */
    const orgaoSigla = window._dados.orgao_sigla;

    /**
     * Opções de paginação disponíveis.
     *
     *  @type {Number[]}
     */
    const paginacao = window._dados.paginacao;

    return { ambiente, appNome, appNomeCompleto, appVersao, orgaoSigla, paginacao };
});
