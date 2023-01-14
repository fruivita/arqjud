/**
 * Helpers para manipulação de dados do objeto.
 */

import { useTranslationsStore } from '@/Stores/TranslationsStore';

function numeroCaixa(caixa) {
    let texto = `${caixa.numero}/${caixa.ano}/GP:${gp(caixa)}`;

    if (caixa.localidade_criadora) {
        texto = `${texto}/${caixa.localidade_criadora.nome}`;
    }

    if (caixa.complemento) {
        texto = `${texto}/${caixa.complemento}`;
    }

    return texto;
}

function gp(caixa) {
    const __ = useTranslationsStore().__;

    return caixa.guarda_permanente ? __('Sim') : __('Não');
}

export { numeroCaixa, gp };
