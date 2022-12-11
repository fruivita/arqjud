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
}

export default Processo;
