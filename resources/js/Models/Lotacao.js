/**
 * Encapsulamento de funcionalidades relativas à lotação pra uso no client
 * side.
 */

import { useTranslationsStore } from '@/Stores/TranslationsStore';

class Lotacao {
    constructor(attributes = {}) {
        Object.assign(this, attributes);
    }

    /**
     * Nome da lotação para exibição.
     *
     * @return {String}
     */
    nomeExibicao() {
        let exibir = '';

        if (this.sigla) {
            exibir = this.sigla;
        }

        if (this.nome) {
            exibir = exibir ? `${exibir} - ${this.nome}` : this.nome;
        }

        return exibir;
    }

    /**
     * String para exibição se a lotação é ou não administrável.
     *
     * @return {String}
     */
    eAdministravel() {
        const __ = useTranslationsStore().__;

        return this.administravel ? __('Sim') : __('Não');
    }
}

export default Lotacao;
