/**
 * Lógica comum da exclusão de items.
 *
 * COMPOSABLE SEM TESTE UNITÁRIO. CUIDADO AO ALTERÁ-LO.
 *
 * @see https://vuejs.org/guide/introduction.html
 * @see https://jsdoc.app/index.html
 * @see https://vue-toastification.maronato.dev/
 */

import { flash } from '@/Composables/UseFlash';
import { exibirModalKey, fecharModalKey } from '@/keys.js';
import { Inertia } from '@inertiajs/inertia';
import { provide, readonly, ref } from 'vue';

export const useExclusao = () => {
    const item = ref('');
    const exibirModal = ref(false);
    const urlDelete = ref('');

    const fecharModal = () => reset();
    provide(exibirModalKey, readonly(exibirModal));
    provide(fecharModalKey, fecharModal);

    const titulo = () => item.value;
    const confirmarExclusao = (url, titulo) => {
        exibirModal.value = true;
        urlDelete.value = url;
        item.value = titulo;
    };

    const excluir = () => {
        Inertia.delete(urlDelete.value, {
            preserveScroll: true,
            preserveState: true,

            onSuccess: () => flash(),
            onFinish: () => reset(),
        });
    };

    const reset = () => {
        exibirModal.value = false;
        urlDelete.value = '';
        item.value = '';
    };

    return { confirmarExclusao, excluir, titulo };
};
