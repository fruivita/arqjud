/**
 * Helpers para manipulação de dados do objeto.
 */

import { nomeLotacao } from '@/Helpers/Lotacao';
import { useTranslationsStore } from '@/Stores/TranslationsStore';

function localizacao(processo) {
    const __ = useTranslationsStore().__;

    return processo.solicitacao_ativa && processo.solicitacao_ativa.entregue_em
        ? nomeLotacao(processo.solicitacao_ativa.destino)
        : __('No arquivo');
}

function gp(processo) {
    const __ = useTranslationsStore().__;

    return processo.guarda_permanente ? __('Sim') : __('Não');
}

export { localizacao, gp };
