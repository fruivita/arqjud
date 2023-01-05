/**
 * Encapsulamento de funcionalidades relativas ao processo pra uso no client
 * side.
 */

import Lotacao from '@/Models/Lotacao';
import { useTranslationsStore } from '@/Stores/TranslationsStore';

class Processo {
    constructor(attributes = {}) {
        Object.assign(this, attributes);
    }

    /**
     * Localização atual do processo.
     *
     * @return {String}
     */
    localizacao() {
        const __ = useTranslationsStore().__;

        return this.solicitacao_ativa && this.solicitacao_ativa.entregue_em
            ? new Lotacao(this.solicitacao_ativa.lotacao_destinataria).nomeExibicao()
            : __('No arquivo');
    }

    /**
     * String para exibição se o processo é de guarda permanente.
     *
     * @return {String}
     */
    gp() {
        const __ = useTranslationsStore().__;

        return this.guarda_permanente ? __('Sim') : __('Não');
    }
}

export default Processo;
