/**
 * Encapsulamento de funcionalidades relativas à caixa pra uso no client side.
 */

import { useTranslationsStore } from '@/Stores/TranslationsStore';

class Caixa {
    constructor(attributes = {}) {
        Object.assign(this, attributes);
    }

    /**
     * Nome completo da caixa para exibição.
     *
     * @return {String}
     */
    numeroExibicao() {
        let texto = `${this.numero}/${this.ano}/GP:${this.gp()}`;

        if (this.localidade_criadora) {
            texto = `${texto}/${this.localidade_criadora.nome}`;
        }

        if (this.complemento) {
            texto = `${texto}/${this.complemento}`;
        }

        return texto;
    }

    /**
     * String para exibição se a caixa é de guarda permanente.
     *
     * @return {String}
     */
    gp() {
        const __ = useTranslationsStore().__;

        return this.guarda_permanente ? __('Sim') : __('Não');
    }
}

export default Caixa;
