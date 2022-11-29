/**
 * Exibe mensagem de notificação no padrão toast.
 *
 * @see https://vuejs.org/guide/introduction.html
 * @see https://github.com/VitorLuizC/vue-uuid
 * @see https://jsdoc.app/index.html
 * @see https://vue-toastification.maronato.dev/
 */

import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { usePage } from '@inertiajs/inertia-vue3';
import { first, keys, toLower } from 'lodash';
import { useToast } from 'vue-toastification';

/**
 * Exibe uma notificação popup (toast) sobre a solicitação do usuário.
 *
 * Se a notificação for informada, ela será exibida, caso contrário, irá
 * exibir a notificação gerada pelo backend.
 *
 * @param {Object} notificacao { tipo : mensagem }
 */
export const flash = (notificacao) => {
    const sucesso = () => toast.success(mensagem);
    const informacao = () => toast.info(mensagem);
    const alerta = () => toast.warning(mensagem);
    const erro = () => toast.error(mensagem);
    const handler = { sucesso, informacao, alerta, erro };

    const toast = useToast();
    // objeto no padrão  { tipo : mensagem }
    notificacao = notificacao || usePage().props.value.flash;
    let mensagem;

    try {
        // tipo de mensagem e a mensagem de fato que será exibida
        const tipo = toLower(first(keys(notificacao)));
        mensagem = notificacao[tipo];

        return handler[tipo]();
    } catch (error) {
        const __ = useTranslationsStore().__;
        console.error(__('Falha na notificação: :erro', { erro: error }));

        return -1;
    }
};
