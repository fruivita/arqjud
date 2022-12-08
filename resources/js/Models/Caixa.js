/**
 * Encapsulamento de funcionalidades relativas à caixa pra uso no client side.
 */

import { toLower } from 'lodash';

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
        let texto = `${this.numero}/${this.ano}/GP:${this.guarda_permanente}`;

        if (this.localidade_criadora) {
            texto = `${texto}/${this.localidade_criadora.nome}`;
        }

        if (this.complemento) {
            texto = `${texto}/${this.complemento}`;
        }

        return texto;
    }

    /**
     * Caixa de guarda permanente?
     *
     * @return {Boolean}
     */
    gp() {
        return toLower(this.guarda_permanente) === 'sim' ? true : false;
    }
}

export default Caixa;
