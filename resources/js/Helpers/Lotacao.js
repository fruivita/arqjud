/**
 * Helpers para manipulação de dados do objeto.
 */

import { useTranslationsStore } from '@/Stores/TranslationsStore';

function nomeLotacao(lotacao) {
    let texto = '';

    if (lotacao.sigla) {
        texto = lotacao.sigla;
    }

    if (lotacao.nome) {
        texto = texto ? `${texto} - ${lotacao.nome}` : lotacao.nome;
    }

    return texto;
}

function eAdministravel(lotacao) {
    const __ = useTranslationsStore().__;

    return lotacao.administravel ? __('Sim') : __('Não');
}

export { nomeLotacao, eAdministravel };
