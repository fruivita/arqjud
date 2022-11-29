/**
 * Store para disponibilização global do status de processamento da requisição.
 *
 * @see https://vuejs.org/
 * @see https://pinia.vuejs.org/
 */

import { Inertia } from '@inertiajs/inertia';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useStatusRequisicaoStore = defineStore('StatusRequisicaoStore', () => {
    Inertia.on('start', () => setStatus(true));
    Inertia.on('finish', () => setStatus(false));

    /**
     * Há requisição em andamento, isto é, sendo processada?
     *
     * - True: requisição em andamento.
     * - False: requisição concluída.
     *
     *  @type {boolean}
     */
    const processando = ref(false);

    /**
     * Define o status da requisição.
     *
     * @param {boolean} status
     */
    const setStatus = (status) => {
        processando.value = status;
    };

    return { processando, setStatus };
});
