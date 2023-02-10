/**
 * Helpers para manipulação de dados do objeto.
 */

import { nomeLotacao } from '@/Helpers/Lotacao';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { first } from 'lodash';

function localizacao(processo) {
    const __ = useTranslationsStore().__;
    const solicitacao = first(processo.solicitacao_ativa);

    return solicitacao?.entregue_em ? nomeLotacao(solicitacao.destino) : __('No arquivo');
}

function gp(processo) {
    const __ = useTranslationsStore().__;

    return processo.guarda_permanente ? __('Sim') : __('Não');
}

export { localizacao, gp };
